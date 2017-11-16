<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace PhlyTest\Expressive\OAuth2ClientAuthentication;

use League\OAuth2\Client\Provider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Phly\Expressive\OAuth2ClientAuthentication\Debug\DebugResourceOwner;
use Phly\Expressive\OAuth2ClientAuthentication\Exception;
use Phly\Expressive\OAuth2ClientAuthentication\OAuth2Adapter;
use Phly\Expressive\OAuth2ClientAuthentication\OAuth2ProviderFactory;
use Phly\Expressive\OAuth2ClientAuthentication\OAuth2User;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionMiddleware;

class OAuth2AdapterTest extends TestCase
{
    public function setUp()
    {
        $this->providerFactory = $this->prophesize(OAuth2ProviderFactory::class);
    }

    public function createNoOpCallback() : callable
    {
        return function () {
        };
    }

    public function testAuthenticateReturnsUserDiscoveredInSession()
    {
        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn([
            'user' => [
                'username' => 'foobar',
                'other' => 'data',
            ],
        ]);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $this->createNoOpCallback(),
            $this->createNoOpCallback()
        );

        $result = $adapter->authenticate($request->reveal());

        $this->assertInstanceOf(OAuth2User::class, $result);
        $this->assertEquals('foobar', $result->getUsername());
        $this->assertArrayHasKey('other', $result->getUserData());
    }

    public function testErrorsProvidedByOAuth2ProviderAreRaisedAsAnException()
    {
        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn([]);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);
        $request->getQueryParams()->willReturn([
            'error' => 'Error raised by provider',
        ]);

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $this->createNoOpCallback(),
            $this->createNoOpCallback()
        );

        $this->expectException(Exception\OAuth2ProviderException::class);
        $this->expectExceptionMessage('Error raised by provider');
        $this->expectExceptionCode(401);
        $result = $adapter->authenticate($request->reveal());
    }

    public function testReturnsNullForMissingProviderTypeRequestAttribute()
    {
        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn([]);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);
        $request->getQueryParams()->willReturn([]);
        $request
            ->getAttribute('provider')
            ->willReturn(null);

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $this->createNoOpCallback(),
            $this->createNoOpCallback()
        );

        $this->assertNull($adapter->authenticate($request->reveal()));
    }

    public function authorizationRequestParams()
    {
        return [
            'empty' => [[]],
            'with-redirect' => [['redirect' => 'https://example.com/origin']],
        ];
    }

    /**
     * @dataProvider authorizationRequestParams
     */
    public function testReturnsNullAndUpdatesSessionWhenRequestingAuthorization($queryParams)
    {
        $providerType = 'unit-test';
        $providerState = 'authenticate';
        $authUrl = 'https://oauth2.example.com/';
        $sessionData = [
            'state' => $providerState,
            'authorization_url' => $authUrl,
        ];
        if (isset($queryParams['redirect'])) {
            $sessionData['redirect'] = $queryParams['redirect'];
        }

        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn([]);
        $session->set('auth', $sessionData)->shouldBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);
        $request->getQueryParams()->willReturn($queryParams);
        $request
            ->getAttribute('provider')
            ->willReturn($providerType);

        $provider = $this->prophesize(AbstractProvider::class);
        $provider->getAuthorizationUrl()->willReturn($authUrl);
        $provider->getState()->willReturn($providerState);

        $this->providerFactory->createProvider($providerType)->will([$provider, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $this->createNoOpCallback(),
            $this->createNoOpCallback()
        );

        $this->assertNull($adapter->authenticate($request->reveal()));
    }

    public function invalidProviderStates()
    {
        return [
            //               [query params, session data]
            'empty-empty' => [[],                          []],
            'state-empty' => [['state' => 'authenticate'], []],
            'state-state' => [['state' => 'authenticate'], ['state' => 'different']],
        ];
    }

    /**
     * @dataProvider invalidProviderStates
     */
    public function testCodeReturnedWithInvalidOAuth2StateResultsInUnauthorizedResult(
        array $queryParams,
        array $sessionData
    ) {
        $queryParams = array_merge($queryParams, [
            'code' => 'oauth2-authorization-token',
        ]);
        $providerType = 'unit-test';

        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn($sessionData);
        $session->set('auth', Argument::any())->shouldNotBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);
        $request->getQueryParams()->willReturn($queryParams);
        $request
            ->getAttribute('provider')
            ->willReturn($providerType);

        $provider = $this->prophesize(AbstractProvider::class);
        $provider->getAuthorizationUrl()->shouldNotBeCalled();
        $provider->getState()->shouldNotBeCalled();

        $this->providerFactory->createProvider($providerType)->will([$provider, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $this->createNoOpCallback(),
            $this->createNoOpCallback()
        );

        $this->assertNull($adapter->authenticate($request->reveal()));
    }

    public function testExceptionWhenRetrievingAccessTokenRaisesNewException()
    {
        $providerState = 'authenticate';
        $providerType = 'unit-test';
        $queryParams = [
            'code' => 'oauth2-authorization-token',
            'state' => $providerState,
        ];
        $sessionData = [
            'state' => $providerState,
        ];

        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn($sessionData);
        $session->set('auth', Argument::any())->shouldNotBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);
        $request->getQueryParams()->willReturn($queryParams);
        $request
            ->getAttribute('provider')
            ->willReturn($providerType);

        $accessTokenException = new RuntimeException('thrown');

        $provider = $this->prophesize(AbstractProvider::class);
        $provider
            ->getAccessToken('authorization_code', ['code' => $queryParams['code']])
            ->willThrow($accessTokenException);

        $this->providerFactory->createProvider($providerType)->will([$provider, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $this->createNoOpCallback(),
            $this->createNoOpCallback()
        );

        $this->expectException(Exception\OAuth2ProviderException::class);
        $this->expectExceptionMessage('thrown');
        $this->expectExceptionCode(401);
        $adapter->authenticate($request->reveal());
    }

    public function testExceptionWhenRetrievingResourceOwnerRaisesNewException()
    {
        $providerState = 'authenticate';
        $providerType = 'unit-test';
        $queryParams = [
            'code' => 'oauth2-authorization-token',
            'state' => $providerState,
        ];
        $sessionData = [
            'state' => $providerState,
        ];

        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn($sessionData);
        $session->set('auth', Argument::any())->shouldNotBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);
        $request->getQueryParams()->willReturn($queryParams);
        $request
            ->getAttribute('provider')
            ->willReturn($providerType);

        $resourceOwnerException = new RuntimeException('thrown');

        $accessToken = $this->prophesize(AccessToken::class);

        $provider = $this->prophesize(AbstractProvider::class);
        $provider
            ->getAccessToken('authorization_code', ['code' => $queryParams['code']])
            ->will([$accessToken, 'reveal']);
        $provider
            ->getResourceOwner(Argument::that([$accessToken, 'reveal']))
            ->willThrow($resourceOwnerException);

        $this->providerFactory->createProvider($providerType)->will([$provider, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $this->createNoOpCallback(),
            $this->createNoOpCallback()
        );

        $this->expectException(Exception\OAuth2ProviderException::class);
        $this->expectExceptionMessage('thrown');
        $this->expectExceptionCode(401);
        $adapter->authenticate($request->reveal());
    }

    public function resourceOwners()
    {
        $sessionTypes = [
            'no-redirect' => [],
            'redirect' => ['redirect' => '/some/pageA,']
        ];
        $github = new Provider\GithubResourceOwner(['email' => 'joe@example.com']);
        $google = new Provider\GoogleUser(['emails' => [
            ['value' => 'joe@example.com'],
        ]]);
        $instagram = new Provider\InstagramResourceOwner(['data' => ['username' => 'joeexamplecom']]);
        $debug = new DebugResourceOwner();

        foreach ($sessionTypes as $key => $sessionData) {
            $name = 'github-' . $key;
            yield $name => ['github', $github, $github->getEmail(), $sessionData];

            $name = 'google-' . $key;
            yield $name => ['google', $google, $google->getEmail(), $sessionData];

            $name = 'instagram-' . $key;
            yield 'instagram' => ['instagram', $instagram, $instagram->getNickname(), $sessionData];

            $name = 'debug-' . $key;
            yield 'debug' => ['debug', $debug, DebugResourceOwner::USER_ID, $sessionData];
        }
    }

    /**
     * @dataProvider resourceOwners
     */
    public function testSuccessfulProviderAuthorizationSetsUserDataInSession(
        string $providerType,
        ResourceOwnerInterface $resourceOwner,
        string $username,
        array $sessionData
    ) {
        $providerState = 'authenticate';
        $queryParams = [
            'code' => 'oauth2-authorization-token',
            'state' => $providerState,
        ];
        $sessionData = array_merge($sessionData, [
            'state' => $providerState,
        ]);
        $newSessionData = $sessionData;
        $newSessionData['user'] = $resourceOwner->toArray();
        $newSessionData['user']['username'] = $username;
        $newSessionData['redirect'] = $sessionData['redirect'] ?? '/';

        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn($sessionData);
        $session->set('auth', $newSessionData)->shouldBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);
        $request->getQueryParams()->willReturn($queryParams);
        $request
            ->getAttribute('provider')
            ->willReturn($providerType);

        $accessToken = $this->prophesize(AccessToken::class);

        $provider = $this->prophesize(AbstractProvider::class);
        $provider
            ->getAccessToken('authorization_code', ['code' => $queryParams['code']])
            ->will([$accessToken, 'reveal']);
        $provider
            ->getResourceOwner(Argument::that([$accessToken, 'reveal']))
            ->willReturn($resourceOwner);

        $this->providerFactory->createProvider($providerType)->will([$provider, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $this->createNoOpCallback(),
            $this->createNoOpCallback()
        );

        $this->assertNull($adapter->authenticate($request->reveal()));
    }

    public function testSuccessfulAuthorizationResultsInRedirectToSessionRedirectValue()
    {
        $sessionData = [
            'user' => ['username' => 'joe@example.com'],
            'redirect' => '/some/page',
        ];
        $newSessionData = $sessionData;
        unset($newSessionData['redirect']);

        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn($sessionData);
        $session->set('auth', $newSessionData)->shouldBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $redirectResponseFactory = function ($redirect) use ($sessionData, $response) {
            $this->assertSame($sessionData['redirect'], $redirect);
            return $response;
        };

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $this->createNoOpCallback(),
            $redirectResponseFactory
        );

        $this->assertSame($response, $adapter->unauthorizedResponse($request->reveal()));
    }

    public function testRequestForAuthorizationResultsInRedirectToProviderAuthorizationUrl()
    {
        $sessionData = [
            'authorization_url' => 'https://oauth2.example.com/',
        ];
        $newSessionData = $sessionData;
        unset($newSessionData['authorization_url']);

        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn($sessionData);
        $session->set('auth', $newSessionData)->shouldBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $redirectResponseFactory = function ($redirect) use ($sessionData, $response) {
            $this->assertSame($sessionData['authorization_url'], $redirect);
            return $response;
        };

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $this->createNoOpCallback(),
            $redirectResponseFactory
        );

        $this->assertSame($response, $adapter->unauthorizedResponse($request->reveal()));
    }

    public function testUnsuccesfulOrIncompleteAuthenticationResultsInUnauthorizedResponse()
    {
        $session = $this->prophesize(SessionInterface::class);
        $session->get('auth')->willReturn([]);
        $session->set('auth', Argument::any())->shouldNotBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE)
            ->will([$session, 'reveal']);

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $unauthorizedResponseFactory = function ($req) use ($request, $response) {
            $this->assertSame($request->reveal(), $req);
            return $response;
        };

        $adapter = new OAuth2Adapter(
            $this->providerFactory->reveal(),
            $unauthorizedResponseFactory,
            $this->createNoOpCallback()
        );

        $this->assertSame($response, $adapter->unauthorizedResponse($request->reveal()));
    }
}
