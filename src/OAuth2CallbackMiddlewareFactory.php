<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Zend\Expressive\AppFactory;
use Zend\Expressive\Authentication\AuthenticationMiddleware;
use Zend\Expressive\Container\ApplicationFactory;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\PathBasedRoutingMiddleware;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Session\SessionMiddleware;

/**
 * Factory for providing the OAuth2 provider callback endpoints within your application.
 *
 * This should be piped under a base path of your application that matches the
 * config key oauth2clientauthentication.auth_url. As an example:
 *
 * <code>
 * // In config/pipeline.php:
 * $app->pipe('/auth', \Phly\Expressive\OAuth2ClientAuthentication\OAuth2CallbackMiddleware);
 *
 * // In config/oauth2clientauthentication.global.php:
 * use Phly\Expressive\OAuth2ClientAuthentication\OAuth2CallbackMiddleware;
 * use Phly\Expressive\OAuth2ClientAuthentication\OAuth2CallbackMiddlewareFactory;
 *
 * return [
 *     'dependencies' => [
 *         'factories' => [
 *             OAuth2CallbackMiddleware::class => OAuth2CallbackMiddlewareFactory::class,
 *         ],
 *     ],
 *     'oauth2clientauthentication' => [
 *         'auth_url' => '/auth',
 *         // ...
 *     ],
 * ];
 * </code>
 *
 * You may also provide alternate route strings:
 *
 * <code>
 * 'oauth2clientauthentication' => [
 *     'routes' => [
 *         // Production route for providers and their callbacks:
 *         'production' => '/:provider[/callback]',
 *         // Debug route for providers and their callbacks:
 *         'debug' => '/:provider[/callback]',
 *     ],
 *     'debug' =>> [
 *         // Authorization route for the debug provider:
 *         'authorization_url' => '/debug/verify',
 *     ]
 * ],
 * </code>
 */
class OAuth2CallbackMiddlewareFactory
{
    public const ROUTE_DEBUG = '/{provider:debug|facebook|github|google|instagram|linkedin}[/oauth2callback]';
    public const ROUTE_DEBUG_AUTHORIZE = '/debug/authorize';
    public const ROUTE_PROD = '/{provider:facebook|github|google|instagram|linkedin}[/oauth2callback]';

    public function __invoke(ContainerInterface $container) : MiddlewareInterface
    {
        /** @var \Zend\Expressive\Application $pipeline */
        $pipeline = (new ApplicationFactory())($container);

        $config = $container->has('config') ? $container->get('config') : [];
        $debug  = $config['debug'] ?? false;
        $routes = $config['oauth2clientauthentication']['routes'] ?? [];
        $route  = $this->getRouteFromConfig($routes, (bool) $debug);

        // OAuth2 providers rely on session to persist the user details
        $pipeline->pipe(SessionMiddleware::class);
        $pipeline->get($route, AuthenticationMiddleware::class);

        if ($debug) {
            $path = $config['oauth2clientauthentication']['debug']['authorization_url'] ?? self::ROUTE_DEBUG_AUTHORIZE;
            $pipeline->get($path, Debug\DebugProviderMiddleware::class);
        }

        $pipeline->pipe(PathBasedRoutingMiddleware::class);
        $pipeline->pipe(DispatchMiddleware::class);

        return $pipeline;
    }

    private function getRouteFromConfig(array $routes, bool $debug) : string
    {
        if ($debug) {
            return $routes['debug'] ?? self::ROUTE_DEBUG;
        }

        return $routes['production'] ?? self::ROUTE_PROD;
    }
}
