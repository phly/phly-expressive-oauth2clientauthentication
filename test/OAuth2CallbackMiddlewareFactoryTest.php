<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace PhlyTest\Expressive\OAuth2ClientAuthentication;

use Phly\Expressive\OAuth2ClientAuthentication\Debug\DebugProviderMiddleware;
use Phly\Expressive\OAuth2ClientAuthentication\OAuth2CallbackMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionProperty;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\Middleware\PathBasedRoutingMiddleware;
use Zend\Expressive\Router\RouterInterface;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Stratigility\MiddlewarePipe;

class OAuth2CallbackMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var MiddlewarePipe */
    private $pipeline;

    /** @var OAuth2CallbackMiddlewareFactory */
    private $factory;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $runner = $this->prophesize(RequestHandlerRunner::class);
        $router = $this->prophesize(RouterInterface::class);
        $routeMiddleware = new PathBasedRoutingMiddleware($router->reveal());

        $this->pipeline = new MiddlewarePipe();

        $middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($this->container->reveal()));
        $this->container->get(MiddlewareFactory::class)->willReturn($middlewareFactory);
        $this->container->get(\Zend\Expressive\ApplicationPipeline::class)->willReturn($this->pipeline);
        $this->container->get(PathBasedRoutingMiddleware::class)->willReturn($routeMiddleware);
        $this->container->get(RequestHandlerRunner::class)->will([$runner, 'reveal']);

        $this->factory = new OAuth2CallbackMiddlewareFactory();
    }

    public function assertContainsExpectedRoute(string $path, Application $pipeline)
    {
        $routes = $pipeline->getRoutes();

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

    public function assertPipelineContainsExpectedCountOfMiddleware()
    {
        $r = new ReflectionProperty($this->pipeline, 'pipeline');
        $r->setAccessible(true);
        $pipeline = $r->getValue($this->pipeline);

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
        $this->assertPipelineContainsExpectedCountOfMiddleware();
    }

    public function testServiceFactoryProducesPipelineWithNoDebugFlagInConfig()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(Application::class, $middleware);

        $this->assertContainsExpectedRoute(OAuth2CallbackMiddlewareFactory::ROUTE_PROD, $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware();
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
        $this->assertPipelineContainsExpectedCountOfMiddleware();
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
        $this->assertPipelineContainsExpectedCountOfMiddleware();
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
        $this->assertPipelineContainsExpectedCountOfMiddleware();
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
        $this->assertPipelineContainsExpectedCountOfMiddleware();
    }
}
