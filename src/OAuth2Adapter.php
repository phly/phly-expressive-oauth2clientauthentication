<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Phly\Mezzio\OAuth2ClientAuthentication\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function array_merge;
use function is_array;
use function is_string;
use function method_exists;

class OAuth2Adapter implements AuthenticationInterface
{
    /** @var OAuth2ProviderFactory */
    private $providerFactory;

    /** @var callable */
    private $redirectResponseFactory;

    /** @var callable */
    private $unauthorizedResponseFactory;

    public function __construct(
        OAuth2ProviderFactory $providerFactory,
        callable $unauthorizedResponseFactory,
        callable $redirectResponseFactory
    ) {
        $this->providerFactory             = $providerFactory;
        $this->unauthorizedResponseFactory = $unauthorizedResponseFactory;
        $this->redirectResponseFactory     = $redirectResponseFactory;
    }

    /**
     * Authenticate the PSR-7 request and return a valid user
     * or null if not authenticated.
     *
     * In the case of a successful authorization request from the provider,
     * this method will still return null; this is to allow us to redirect to
     * the original page requesting authorization.
     *
     * On subsequent requests, this method will return the authenticated
     * user as retrieved from the session.
     */
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        // Have we authenticated before? If so, return the authenticated user.
        if ($this->isAuthenticatedSession($session)) {
            return $this->getUserFromSession($session);
        }

        $params = $request->getQueryParams();

        // If the parameters indicate an error, we should raise an exception.
        if (! empty($params['error'])) {
            return $this->processError($params['error']);
        }

        // Is the authentication request from a known route that defines the
        // provider to use? If not, we need to display the authentication page.
        $providerType = $request->getAttribute('provider');
        if (null === $providerType) {
            return null;
        }

        $provider = $this->providerFactory->createProvider($providerType);

        $oauth2SessionData = $session->get('auth');
        $oauth2SessionData = is_array($oauth2SessionData) ? $oauth2SessionData : [];

        // No code present in query string, meaning we need to request one from
        // the OAuth2 provider. We'll set the session state, so that we can send
        // a redirect via the unauthorized response.
        if (empty($params['code'])) {
            return $this->requestAuthorization(
                $provider,
                $session,
                $oauth2SessionData,
                $params['redirect'] ?? ''
            );
        }

        // No oauth2 state present, so simply unauthorized
        if (
            empty($params['state'])
            || ! isset($oauth2SessionData['state'])
            || $params['state'] !== $oauth2SessionData['state']
        ) {
            return null;
        }

        // Handling redirect from OAuth2 provider
        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $params['code'],
            ]);

            $resourceOwner = $provider->getResourceOwner($token);
        } catch (Exception $e) {
            return $this->processError($e);
        }

        // Authenticated! Store details in session so we can redirect to the
        // page requesting authorization.
        $oauth2SessionData['user'] = array_merge(
            $resourceOwner->toArray(),
            ['username' => $this->getUsernameFromResourceOwner($resourceOwner)]
        );

        $oauth2SessionData['redirect'] = $oauth2SessionData['redirect'] ?? '/';

        $session->set('auth', $oauth2SessionData);

        return null;
    }

    /**
     * Generate the unauthorized response.
     *
     * In some cases, this is simply generating redirects:
     *
     * - if a request for authorization has been made
     * - if we've received a valid authorization from the provider
     *
     * Otherwise, we display the login page.
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $oauth2SessionData = $session->get('auth');

        // Successfully authorized; time to redirect
        if (
            is_array($oauth2SessionData)
            && isset($oauth2SessionData['user'])
            && isset($oauth2SessionData['redirect'])
        ) {
            $redirect = $oauth2SessionData['redirect'];
            unset($oauth2SessionData['redirect']);
            $session->set('auth', $oauth2SessionData);
            return ($this->redirectResponseFactory)($redirect);
        }

        // Request for authorization has been made
        if (is_array($oauth2SessionData) && isset($oauth2SessionData['authorization_url'])) {
            $authorizationUrl = $oauth2SessionData['authorization_url'];
            unset($oauth2SessionData['authorization_url']);
            $session->set('auth', $oauth2SessionData);
            return ($this->redirectResponseFactory)($authorizationUrl);
        }

        // No credentials or prior authorization request present
        return ($this->unauthorizedResponseFactory)($request);
    }

    private function isAuthenticatedSession(SessionInterface $session): bool
    {
        $data = $session->get('auth');
        return is_array($data) && isset($data['user']['username']);
    }

    private function getUserFromSession(SessionInterface $session): UserInterface
    {
        $data     = $session->get('auth');
        $username = $data['user']['username'];
        return new OAuth2User($username, $data['user']);
    }

    /**
     * @param string|Throwable $error
     * @throws Exception\OAuth2ProviderException
     */
    private function processError(mixed $error)
    {
        if (is_string($error)) {
            throw Exception\OAuth2ProviderException::forErrorString($error);
        }
        throw Exception\OAuth2ProviderException::forThrowable($error);
    }

    private function requestAuthorization(
        AbstractProvider $provider,
        SessionInterface $session,
        array $sessionData,
        string $redirect
    ) {
        // Authorization URL MUST be generated BEFORE we retrieve the state,
        // as it is responsible for generating the state in the first place!
        $authorizationUrl = $provider->getAuthorizationUrl();

        if (! empty($redirect)) {
            $sessionData['redirect'] = $redirect;
        }

        $sessionData['state']             = $provider->getState();
        $sessionData['authorization_url'] = $authorizationUrl;
        $session->set('auth', $sessionData);
    }

    private function getUsernameFromResourceOwner(ResourceOwnerInterface $resourceOwner): ?string
    {
        if (method_exists($resourceOwner, 'getEmail')) {
            // All official providers except Instagram
            return $resourceOwner->getEmail();
        }

        if (method_exists($resourceOwner, 'getNickname')) {
            // Instagram
            return $resourceOwner->getNickname();
        }

        // If none of the methods above exists, getId() is always present in a ResourceOwner.
        return $resourceOwner->getId();
    }
}
