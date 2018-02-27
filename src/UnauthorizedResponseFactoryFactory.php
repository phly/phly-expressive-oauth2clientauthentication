<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Generate a callable capable of producing an "unauthorized" response.
 *
 * This implementation will generate a templated 401 response, passing the
 * following values as view parameters:
 *
 * - auth_path: the base path to the various OAuth2 provider callbacks.
 * - redirect: the URI of the page resulting in the unauthorized response.
 * - debug: whether or not to display the debug provider.
 *
 * The auth_path may be provided via the config service, as the key
 * oauth2clientauthentication.auth_path. If not provided, it defaults to
 * "/auth".
 *
 * The debug flag may be provided via the config service, as the key debug.
 *
 * Provide your template via the oauth2clientauthentication::401 template.
 */
class UnauthorizedResponseFactoryFactory
{
    public const DEFAULT_AUTH_PATH = '/auth';
    public const DEFAULT_TEMPLATE = 'oauth2clientauthentication::401';

    public function __invoke(ContainerInterface $container) : callable
    {
        return function (Request $request) use ($container) : Response {
            $originalRequest = $request->getAttribute('originalRequest', $request);

            $config = $container->has('config') ? $container->get('config') : [];
            $debug  = array_key_exists('debug', $config) ? $config['debug'] : false;
            $authPath = $config['oauth2clientauthentication']['auth_path'] ?? self::DEFAULT_AUTH_PATH;

            $view = [
                'auth_path' => (string) $request->getUri()->withPath($authPath),
                'redirect'  => (string) $originalRequest->getUri(),
                'debug'     => (bool) $debug,
            ];

            $response = $container->get(Response::class);
            $renderer = $container->get(TemplateRendererInterface::class);

            $response->getBody()->write(
                $renderer->render(self::DEFAULT_TEMPLATE, $view)
            );
            return $response->withStatus(401);
        };
    }
}
