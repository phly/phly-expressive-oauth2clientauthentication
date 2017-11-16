<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace PhlyTest\Expressive\OAuth2ClientAuthentication\Debug;

use Phly\Expressive\OAuth2ClientAuthentication\Debug\DebugProviderMiddleware;
use Phly\Expressive\OAuth2ClientAuthentication\Debug\DebugProviderMiddlewareFactory;
use Phly\Expressive\OAuth2ClientAuthentication\RedirectResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class DebugProviderMiddlewareFactoryTest extends TestCase
{
    public function testProducesMiddlewareWithoutPathTemplateConfig()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(false)->shouldBeCalled();
        $container->get('config')->shouldNotBeCalled();

        $redirectResponseFactory = function () {
        };

        $container->get(RedirectResponseFactory::class)->willReturn($redirectResponseFactory);

        $factory = new DebugProviderMiddlewareFactory();

        $middleware = $factory($container->reveal());

        $this->assertInstanceOf(DebugProviderMiddleware::class, $middleware);
        $this->assertAttributeSame($redirectResponseFactory, 'redirectResponseFactory', $middleware);
        $this->assertAttributeSame(DebugProviderMiddleware::DEFAULT_PATH_TEMPLATE, 'pathTemplate', $middleware);
    }

    public function testProducesMiddlewareWithConfiguredCallbackPathTemplate()
    {
        $pathTemplate = '/oauth2/debug/callback?code=%s&state=%s';
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true)->shouldBeCalled();
        $container->get('config')->willReturn([
            'oauth2clientauthentication' => [
                'debug' => [
                    'callback_uri_template' => $pathTemplate,
                ],
            ],
        ]);

        $redirectResponseFactory = function () {
        };

        $container->get(RedirectResponseFactory::class)->willReturn($redirectResponseFactory);

        $factory = new DebugProviderMiddlewareFactory();

        $middleware = $factory($container->reveal());

        $this->assertInstanceOf(DebugProviderMiddleware::class, $middleware);
        $this->assertAttributeSame($redirectResponseFactory, 'redirectResponseFactory', $middleware);
        $this->assertAttributeSame($pathTemplate, 'pathTemplate', $middleware);
    }
}
