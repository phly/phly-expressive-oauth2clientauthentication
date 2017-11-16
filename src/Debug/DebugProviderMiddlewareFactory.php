<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication\Debug;

use Phly\Expressive\OAuth2ClientAuthentication\RedirectResponseFactory;
use Psr\Container\ContainerInterface;

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
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $pathTemplate = $config['oauth2clientauthentication']['debug']['callback_uri_template']
            ?? DebugProviderMiddleware::DEFAULT_PATH_TEMPLATE;
        return new DebugProviderMiddleware(
            $container->get(RedirectResponseFactory::class),
            $pathTemplate
        );
    }
}
