<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication;

return [
    'oauth2clientauthentication' => [
        // Configure the various OAuth2 providers.
        //
        // Each OAuth2 provider has its own configuration. You may need to review
        // http://oauth2-client.thephpleague.com/providers/league/ for details
        // on each and what configuration options they accept.

        // Debug
        // This is the debug provider shipped within this component for purposes
        // of testing the OAuth2 client workflow within your applications.
        'debug' => [
            // Provider key must be present for factory creation.
            'provider' => Debug\DebugProvider::class,
            'options' => [
                // Provide this if you have provided an alternate route path via
                // the oauth2clientauthentication.routes.debug key:
                // 'callback_uri_template' => '/alternate/debug/callback?code=%s&state=%s',

                // Provide this if you want to use an alternate path for the OAuth2
                // "server" authorization:
                // 'authorization_url' => '/alternate/debug/authorization',
            ]
        ],

        // Facebook
        // 'facebook' => [
        //     'provider' => Provider\Facebook::class,
        //     'options' => [
        //         'clientId' => '{facebook-app-id}',
        //         'clientSecret' => '{facebook-app-secret}',
        //         'redirectUri' => '', // based on the auth_path + production route; must be fully qualifed
        //         'graphApiVersion' => 'v2.10',
        //     ],
        // ],

        // GitHub
        // 'github' => [
        //     'provider' => Provider\Github::class,
        //     'options' => [
        //         'clientId' => '{github-client-id}',
        //         'clientSecret' => '{github-client-secret}',
        //         'redirectUri' => '', // based on the auth_path + production route; must be fully qualifed
        //     ],
        // ],

        // Google
        // 'google' => [
        //     'provider' => Provider\Google::class,
        //     'options' => [
        //         'clientId' => '{google-client-id}',
        //         'clientSecret' => '{google-client-secret}',
        //         'redirectUri' => '', // based on the auth_path + production route; must be fully qualifed
        //         'hostedDomain' => '', // scheme + domain of your app
        //     ],
        // ],

        // Instagram
        // 'instagram' => [
        //     'provider' => Provider\Instagram::class,
        //     'options' => [
        //        'clientId' => '{instagram-client-id}',
        //        'clientSecret' => '{instagram-client-secret}',
        //        'redirectUri' => '', // based on the auth_path + production route; must be fully qualifed
        //        'host' => 'https://api.instagram.com', // Optional; this is the default
        //     ],
        // ],

        // LinkedIn
        // 'linkedin' => [
        //     'provider' => Provider\LinkedIn::class,
        //     'options' => [
        //         'clientId' => '{linkedin-client-id}',
        //         'clientSecret' => '{linkedin-client-secret}',
        //         'redirectUri' => '', // based on the auth_path + production route; must be fully qualifed
        //     ],
        // ],

        // Customized
        // 'custom' => [
        //    'provider' => Provider\GenericProvider::class,
        //    'options' => [
        //        'clientId' => '',
        //        'clientSecret' => '',
        //        'redirectUri' => '',
        //        'urlAuthorize' => '',
        //        'urlAccessToken' => '',
        //        'urlResourceOwnerDetails' => '',
        //    ],
        // ],
    ],
    'dependencies' => [
        'factories' => [
            // Enable this when in debug mode:
            // Debug\DebugProviderMiddleware::class => Debug\DebugProviderMiddlewareFactory::class,
        ],
    ],
];
