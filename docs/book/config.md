# Configuration

In order to use this authentication adapter, you will need to provide
configuration for the OAuth2 providers you plan to use.

Examples are provided in the `config/` directory of this component, and repeated
here for purposes of documentation:

## Global configuration

This is configuration that should be present no matter what environment you are
in, and it covers the base path for the OAuth2 client callbacks and debug
provider URIs.

```php
// e.g. config/autoload/oauth2-client.global.php:

return [
    'oauth2clientauthentication' => [
        // Configure the base path for all OAuth2 client callbacks. By default,
        // this is "/auth".
        // 'auth_path' => '/auth',

        // Configure the production and debug routes for OAuth2 client callbacks
        // if desired. These strings will be relative to the 'auth_path' config
        // as specified above. Provider names in the regex should match the keys 
        // associated with enabled providers in configuration. The "custom" string 
        // is just an example of a provider key named "custom" described below.
        'routes' => [
            // Production path.
            // 'production' => '/{provider:facebook|github|google|instagram|linkedin|custom}[/oauth2callback]',

            // Debug path.
            // 'debug' => '/{provider:debug|facebook|github|google|instagram|linkedin|custom}[/oauth2callback]',
        ],
    ],
];
```

## Local/Environment-specific configuration

This is configuration for the providers you wish to enable. You will need to
review the [league/oauth2-client providers documentation](http://oauth2-client.thephpleague.com/providers/league/
for links to both full configuration documentation, as well as resources on how
to obtain the various client identifiers and secrets you will need to use.

Each provider you define needs at least the provider key with the client class name and 
the options key with an array which is passed to the client constructor.

This information should _not_ be shipped directly in your repository, but rather
included as part of your application environment.

```php
// e.g. config/autoload/oauth2-client.local.php:

return [
    'oauth2clientauthentication' => [
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
        // It's possible to configure any provider that extend the League AbstractProvider, including custom 
        // implementations or extensions. This example uses GenericProvider which can be used with any OAuth 
        // 2.0 Server that uses Bearer tokens.
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
```

## Pipeline configuration

The various callbacks operate under a base path as specified by the
`oauth2clientauthentication.auth_path` configuration, which defaults to `/auth`.
You will need to pipe the `Phly\Expressive\OAuth2ClientAuthentication\OAuth2CallbackMiddleware`
service to that path:

```php
// In config/pipeline.php:

use Phly\Expressive\OAuth2ClientAuthentication\OAuth2CallbackMiddleware;

$app->pipe('/auth', OAuth2CallbackMiddleware::class);
```

@todo Detail how to pipe the callback middleware when using other middleware frameworks.

## Route configuration

Once the above is complete, you can add
`Zend\Expressive\Authentication\AuthenticationMiddleware` to your route-specific
pipelines. You will also need to pipe
`Zend\Expressive\Session\SessionMiddleware` in these pipelines as this adapter
persists user information within the session.

As an example:

```php
// In config/routes.php

use Zend\Expressive\Authentication\AuthenticationMiddleware;
use Zend\Expressive\Session\SessionMiddleware;

$app->post('/api/books', [
    SessionMiddleware::class,
    AuthenticationMiddleware::class,
    CreateBookHandler::class,
]);
```

> ### Create a delegator factory
>
> You may want to consider creating a delegator factory for registering these
> two middleware in a pipeline with any handler. As an example:
>
> ```php
> function (ContainerInterface $container, $serviceName, callable $callback)
> {
>     $pipeline = new MiddlewarePipe();
>     $pipeline->pipe($container->get(SessionMiddleware::class));
>     $pipeline->pipe($container->get(AuthenticationMiddleware::class));
>     $pipeline->pipe($callback());
>     return $pipeline;
> }
> ```
