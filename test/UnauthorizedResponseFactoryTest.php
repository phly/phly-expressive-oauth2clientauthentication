<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace PhlyTest\Expressive\OAuth2ClientAuthentication;

use Phly\Expressive\OAuth2ClientAuthentication\UnauthorizedResponseFactoryFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class UnauthorizedResponseFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new UnauthorizedResponseFactoryFactory();
    }

    public function testServiceFactoryReturnsCallable()
    {
        $responseFactory = ($this->factory)($this->container->reveal());
        $this->assertInternalType('callable', $responseFactory);
    }

    public function configValues()
    {
        return [
            //             [config exists?, config data]
            'no-config'   => [false,        []],
            'no-debug'    => [true,         []],
            'debug-false' => [true,         ['debug' => false]],
            'debug-true'  => [true,         ['debug' => true]],
            'auth-path'   => [true,         ['oauth2clientauthentication' => ['auth_path' => '/oauth2']]],
        ];
    }

    /**
     * @dataProvider configValues
     */
    public function testResponseFactoryReturns302ResponseWithLocationBasedOnUrlArgument(
        bool $hasConfig,
        array $config
    ) {
        $debug = array_key_exists('debug', $config) ? $config['debug'] : false;
        $authPath = $config['oauth2clientauthentication']['auth_path']
            ?? UnauthorizedResponseFactoryFactory::DEFAULT_AUTH_PATH;
        $redirectPath = '/some/path';

        $originalUri = $this->prophesize(UriInterface::class);
        $originalUri->__toString()->willReturn($redirectPath);
        $originalRequest = $this->prophesize(ServerRequestInterface::class);
        $originalRequest->getUri()->will([$originalUri, 'reveal']);

        $uri = $this->prophesize(UriInterface::class);
        $uri
            ->withPath($authPath)
            ->will([$uri, 'reveal']);
        $uri->__toString()->willReturn($authPath);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getAttribute('originalRequest', Argument::that([$request, 'reveal']))
            ->will([$originalRequest, 'reveal']);
        $request->getUri()->will([$uri, 'reveal']);

        $renderer = $this->prophesize(TemplateRendererInterface::class);
        $renderer
            ->render(UnauthorizedResponseFactoryFactory::DEFAULT_TEMPLATE, [
                'auth_path' => $authPath,
                'redirect' => $redirectPath,
                'debug' => $debug,
            ])
            ->willReturn('content');

        $body = $this->prophesize(StreamInterface::class);
        $body->write('content')->shouldBeCalled();

        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->will([$body, 'reveal']);
        $response->withStatus(401)->will([$response, 'reveal']);

        $this->container->has('config')->willReturn($hasConfig)->shouldBeCalled();
        if ($hasConfig) {
            $this->container->get('config')->willReturn($config)->shouldBeCalled();
        }

        $this->container->get(ResponseInterface::class)->willReturn(function () use ($response) {
            return $response->reveal();
        });
        $this->container->get(TemplateRendererInterface::class)->will([$renderer, 'reveal']);

        $factory = ($this->factory)($this->container->reveal());
        $result = $factory($request->reveal());
        $this->assertSame($response->reveal(), $result);
    }
}
