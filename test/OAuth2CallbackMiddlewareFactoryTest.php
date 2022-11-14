<?php

declare(strict_types=1);

namespace PhlyTest\Mezzio\OAuth2ClientAuthentication;

use Laminas\Stratigility\MiddlewarePipe;
use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\MiddlewareContainer;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\Middleware\RouteMiddleware;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionMiddleware;
use Phly\Mezzio\OAuth2ClientAuthentication\Debug\DebugProviderMiddleware;
use Phly\Mezzio\OAuth2ClientAuthentication\OAuth2CallbackMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionProperty;

use function sprintf;

class OAuth2CallbackMiddlewareFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var MiddlewarePipe */
    private $pipeline;

    /** @var OAuth2CallbackMiddlewareFactory */
    private $factory;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $router = new FastRouteRouter();
        $this->container->get(RouterInterface::class)->willReturn($router);

        $sessionMiddleware = $this->prophesize(SessionMiddleware::class)->reveal();
        $this->container->get(SessionMiddleware::class)->willReturn($sessionMiddleware);

        $authMiddleware = $this->prophesize(AuthenticationMiddleware::class)->reveal();
        $this->container->get(AuthenticationMiddleware::class)->willReturn($authMiddleware);

        $dispatchMiddleware = $this->prophesize(DispatchMiddleware::class)->reveal();
        $this->container->get(DispatchMiddleware::class)->willReturn($dispatchMiddleware);

        $middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($this->container->reveal()));
        $this->container->get(MiddlewareFactory::class)->willReturn($middlewareFactory);

        $this->factory = new OAuth2CallbackMiddlewareFactory();
    }

    public function assertContainsExpectedRoute(string $path, MiddlewarePipe $pipeline)
    {
        $routeMiddleware = $this->getRouteMiddlewareFromPipeline($pipeline);
        $routes          = $this->getRoutesFromRouteMiddleware($routeMiddleware);

        $found = false;
        foreach ($routes as $route) {
            if (
                $route->getPath() === $path
                && ['GET'] === $route->getAllowedMethods()
            ) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, sprintf('Route with path "%s" not found in pipeline', $path));
    }

    public function assertPipelineContainsExpectedCountOfMiddleware(MiddlewarePipe $pipeline)
    {
        $r = new ReflectionProperty($pipeline, 'pipeline');
        $r->setAccessible(true);
        $pipeline = $r->getValue($pipeline);

        // Should contain session, routing, and dispatch middleware
        $this->assertCount(3, $pipeline, 'Pipeline does not contain expected count of middleware');
    }

    private function getRouteMiddlewareFromPipeline(MiddlewarePipe $pipeline): MiddlewareInterface
    {
        $r = new ReflectionProperty($pipeline, 'pipeline');
        $r->setAccessible(true);

        foreach ($r->getValue($pipeline) as $middleware) {
            if ($middleware instanceof RouteMiddleware) {
                return $middleware;
            }
        }

        $this->fail('Could not locate route middleware in pipeline!');
    }

    private function getRoutesFromRouteMiddleware(RouteMiddleware $middleware): array
    {
        $r = new ReflectionProperty($middleware, 'router');
        $r->setAccessible(true);
        $router = $r->getValue($middleware);

        $r = new ReflectionProperty($router, 'routesToInject');
        $r->setAccessible(true);
        return $r->getValue($router);
    }

    public function testServiceFactoryProducesPipelineWithNoConfigPresent()
    {
        $this->container->has('config')->willReturn(false);
        $this->container->get('config')->shouldNotBeCalled();

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(MiddlewarePipe::class, $middleware);

        $this->assertContainsExpectedRoute(OAuth2CallbackMiddlewareFactory::ROUTE_PROD, $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware($middleware);
    }

    public function testServiceFactoryProducesPipelineWithNoDebugFlagInConfig()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(MiddlewarePipe::class, $middleware);

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
        $this->assertInstanceOf(MiddlewarePipe::class, $middleware);

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
        $this->assertInstanceOf(MiddlewarePipe::class, $middleware);

        $this->assertContainsExpectedRoute($productionRoute, $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware($middleware);
    }

    public function testServiceFactoryCanUseDebugRouteProvidedViaConfiguration()
    {
        $debugRoute = '/{provider:debug}[/callback]';
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'debug'                      => true,
            'oauth2clientauthentication' => [
                'routes' => [
                    'debug' => $debugRoute,
                ],
            ],
        ]);
        $this->container->has(DebugProviderMiddleware::class)->willReturn(true);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(MiddlewarePipe::class, $middleware);

        $this->assertContainsExpectedRoute($debugRoute, $middleware);
        $this->assertContainsExpectedRoute('/debug/authorize', $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware($middleware);
    }

    public function testServiceFactoryCanUseDebugAuthorizationRouteProvidedViaConfiguration()
    {
        $debugRoute          = '/{provider:debug}[/callback]';
        $debugAuthorizeRoute = '/debug/authorization';
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'debug'                      => true,
            'oauth2clientauthentication' => [
                'routes' => [
                    'debug' => $debugRoute,
                ],
                'debug'  => [
                    'authorization_url' => $debugAuthorizeRoute,
                ],
            ],
        ]);
        $this->container->has(DebugProviderMiddleware::class)->willReturn(true);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(MiddlewarePipe::class, $middleware);

        $this->assertContainsExpectedRoute($debugRoute, $middleware);
        $this->assertContainsExpectedRoute($debugAuthorizeRoute, $middleware);
        $this->assertPipelineContainsExpectedCountOfMiddleware($middleware);
    }
}
