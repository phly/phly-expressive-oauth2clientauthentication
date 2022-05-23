<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication\Debug;

use Phly\Mezzio\OAuth2ClientAuthentication\RedirectResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Produces the DebugProviderMiddleware.
 *
 * You may specify an alternate OAuth2 client callback path to use via configuration:
 *
 * <code>
 * 'oauth2clientauthentication' => [
 *     'debug' => [
 *         'callback_uri_template' => '/oauth2/debug/callback?code=%s&state=%s',
 *     ],
 * ],
 * </code>
 *
 * The URI should be in sprintf format. The code and state parameters MUST be
 * provided as query string arguments, and the code MUST precede the state.
 *
 * The path template should match the URI provided under the config key
 * oauth2clientauthentication.routes.debug.
 */
class DebugProviderMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): MiddlewareInterface
    {
        $config       = $container->has('config') ? $container->get('config') : [];
        $pathTemplate = $config['oauth2clientauthentication']['debug']['callback_uri_template']
            ?? DebugProviderMiddleware::DEFAULT_PATH_TEMPLATE;
        return new DebugProviderMiddleware(
            $container->get(RedirectResponseFactory::class),
            $pathTemplate
        );
    }
}
