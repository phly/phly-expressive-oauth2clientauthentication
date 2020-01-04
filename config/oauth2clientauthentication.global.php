<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Mezzio\OAuth2ClientAuthentication;

return [
    'oauth2clientauthentication' => [
        // Configure the base path for all OAuth2 client callbacks. By default,
        // this is "/auth".
        // 'auth_path' => '/auth',

        // Configure the production and debug routes for OAuth2 client callbacks
        // if desired. These strings will be relative to the 'auth_path' config
        // as specified above.
        'routes' => [
            // Production path.
            // 'production' => '/{provider:facebook|github|google|instagram}|linkedin[/oauth2callback]',

            // Debug path.
            // 'debug' => '/{provider:debug|facebook|github|google|instagram|linkedin}[/oauth2callback]',
        ],
    ],
];
