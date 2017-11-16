<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace PhlyTest\Expressive\OAuth2ClientAuthentication;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Phly\Expressive\OAuth2ClientAuthentication\Debug\DebugProviderMiddleware;
use Phly\Expressive\OAuth2ClientAuthentication\OAuth2CallbackMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionProperty;
use Zend\Expressive\Application;
use Zend\Expressive\Authentication\AuthenticationMiddleware;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Session\SessionMiddleware;

class OAuth2CallbackMiddlewareFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->router = $this->prophesize(RouterInterface::class);
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(RouterInterface::class)->will([$this->router, 'reveal']);
        $this->container->has(SessionMiddleware::class)->willReturn(true);
        $this->container->has(AuthenticationMiddleware::class)->willReturn(true);

        $this->factory = new OAuth2CallbackMiddlewareFactory();
    }

    public function assertContainsExpectedRoute(string $path, Application $pipeline)
    {
        $r = new ReflectionProperty($pipeline, 'routes');
        $r->setAccessible(true);
        $routes = $r->getValue($pipeline);

        $found = false;
        foreach ($routes as $route) {
            if ($route->getPath() === $path
                && ['GET'] === $route->getAllowedMethods()
            ) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, sprintf('Route with path "%s" not found in pipeline', $path));
    }

    public function assertPipelineContainsExpectedCountOfMiddleware(Application $application)
    {
        $r = new ReflectionProperty($application, 'pipeline');
        $r->setAccessible(true);
        $pipeline = $r->getValue($application);

        // Should contain session, routing, and dispatch middleware
        $this->assertCount(3, $pipeline, 'Pipeline does not contain expected count of middleware');
    }

    public function testServiceFactoryProducesPipelineWithNoConfigPresent()
    {
        $this->container->has('config')->willReturn(false);
        $this->container->get('config')->shouldNotBeCalled();

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(Application::class, $middleware);

        $this->assertContainsExpectedRoute(OAuth2CallbackMiddlewareFactory::ROUTE_PROD, $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware($middleware);
    }

    public function testServiceFactoryProducesPipelineWithNoDebugFlagInConfig()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(Application::class, $middleware);

        $this->assertContainsExpectedRoute(OAuth2CallbackMiddlewareFactory::ROUTE_PROD, $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware($middleware);
    }

    public function testServiceFactoryProducesPipelineWithDebugCallbackRouteWhenDebugFlagEnabled()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['debug' => true]);
        $this->container->has(DebugProviderMiddleware::class)->willReturn(true);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(Application::class, $middleware);

        $this->assertContainsExpectedRoute(OAuth2CallbackMiddlewareFactory::ROUTE_DEBUG, $middleware);
        $this->assertContainsExpectedRoute('/debug/authorize', $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware($middleware);
    }

    public function testServiceFactoryCanUseProductionRouteProvidedViaConfiguration()
    {
        $productionRoute = '/{provider:github|instagram}[/callback]';
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'oauth2clientauthentication' => [
                'routes' => [
                    'production' => $productionRoute,
                ],
            ],
        ]);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(Application::class, $middleware);

        $this->assertContainsExpectedRoute($productionRoute, $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware($middleware);
    }

    public function testServiceFactoryCanUseDebugRouteProvidedViaConfiguration()
    {
        $debugRoute = '/{provider:debug}[/callback]';
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'debug' => true,
            'oauth2clientauthentication' => [
                'routes' => [
                    'debug' => $debugRoute,
                ],
            ],
        ]);
        $this->container->has(DebugProviderMiddleware::class)->willReturn(true);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(Application::class, $middleware);

        $this->assertContainsExpectedRoute($debugRoute, $middleware);
        $this->assertContainsExpectedRoute('/debug/authorize', $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware($middleware);
    }

    public function testServiceFactoryCanUseDebugAuthorizationRouteProvidedViaConfiguration()
    {
        $debugRoute = '/{provider:debug}[/callback]';
        $debugAuthorizeRoute = '/debug/authorization';
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'debug' => true,
            'oauth2clientauthentication' => [
                'routes' => [
                    'debug' => $debugRoute,
                ],
                'debug' => [
                    'authorization_url' => $debugAuthorizeRoute,
                ],
            ],
        ]);
        $this->container->has(DebugProviderMiddleware::class)->willReturn(true);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(Application::class, $middleware);

        $this->assertContainsExpectedRoute($debugRoute, $middleware);
        $this->assertContainsExpectedRoute($debugAuthorizeRoute, $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware($middleware);
    }
}
