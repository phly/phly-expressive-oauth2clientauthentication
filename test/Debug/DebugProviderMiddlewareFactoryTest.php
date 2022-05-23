<?php

declare(strict_types=1);

namespace PhlyTest\Mezzio\OAuth2ClientAuthentication\Debug;

use Phly\Mezzio\OAuth2ClientAuthentication\Debug\DebugProviderMiddleware;
use Phly\Mezzio\OAuth2ClientAuthentication\Debug\DebugProviderMiddlewareFactory;
use Phly\Mezzio\OAuth2ClientAuthentication\RedirectResponseFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class DebugProviderMiddlewareFactoryTest extends TestCase
{
    use ProphecyTrait;

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

        $reflection                        = new ReflectionClass($middleware);
        $reflectionRedirectResponseFactory = $reflection->getProperty('redirectResponseFactory');
        $reflectionRedirectResponseFactory->setAccessible(true);
        $reflectionPathTemplate = $reflection->getProperty('pathTemplate');
        $reflectionPathTemplate->setAccessible(true);

        $this->assertInstanceOf(DebugProviderMiddleware::class, $middleware);
        $this->assertSame($redirectResponseFactory, $reflectionRedirectResponseFactory->getValue($middleware));
        $this->assertSame(
            DebugProviderMiddleware::DEFAULT_PATH_TEMPLATE,
            $reflectionPathTemplate->getValue($middleware)
        );
    }

    public function testProducesMiddlewareWithConfiguredCallbackPathTemplate()
    {
        $pathTemplate = '/oauth2/debug/callback?code=%s&state=%s';
        $container    = $this->prophesize(ContainerInterface::class);
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

        $reflection                        = new ReflectionClass($middleware);
        $reflectionRedirectResponseFactory = $reflection->getProperty('redirectResponseFactory');
        $reflectionRedirectResponseFactory->setAccessible(true);
        $reflectionPathTemplate = $reflection->getProperty('pathTemplate');
        $reflectionPathTemplate->setAccessible(true);

        $this->assertInstanceOf(DebugProviderMiddleware::class, $middleware);
        $this->assertSame($redirectResponseFactory, $reflectionRedirectResponseFactory->getValue($middleware));
        $this->assertSame($pathTemplate, $reflectionPathTemplate->getValue($middleware));
    }
}
