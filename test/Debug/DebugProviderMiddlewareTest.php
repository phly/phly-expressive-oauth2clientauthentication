<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace PhlyTest\Expressive\OAuth2ClientAuthentication\Debug;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Phly\Expressive\OAuth2ClientAuthentication\Debug\DebugProvider;
use Phly\Expressive\OAuth2ClientAuthentication\Debug\DebugProviderMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DebugProviderMiddlewareTest extends TestCase
{
    public function testUsesDefaultValuesToProduceRedirectResponse()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $delegate = $this->prophesize(DelegateInterface::class)->reveal();
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $expected = sprintf(
            DebugProviderMiddleware::DEFAULT_PATH_TEMPLATE,
            DebugProvider::CODE,
            DebugProvider::STATE
        );

        $redirectResponseFactory = function ($uri) use ($expected, $response) {
            $this->assertSame($expected, $uri);
            return $response;
        };

        $middleware = new DebugProviderMiddleware($redirectResponseFactory);
        $this->assertSame(
            $response,
            $middleware->process($request, $delegate)
        );
    }

    public function testWillUsesPathTemplateProvidedInConstructorToProduceRedirectResponse()
    {
        $pathTemplate = '/oauth2/debug/callback?code=%s&state=%s';
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $delegate = $this->prophesize(DelegateInterface::class)->reveal();
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $expected = sprintf(
            $pathTemplate,
            DebugProvider::CODE,
            DebugProvider::STATE
        );

        $redirectResponseFactory = function ($uri) use ($expected, $response) {
            $this->assertSame($expected, $uri);
            return $response;
        };

        $middleware = new DebugProviderMiddleware($redirectResponseFactory, $pathTemplate);
        $this->assertSame(
            $response,
            $middleware->process($request, $delegate)
        );
    }
}
