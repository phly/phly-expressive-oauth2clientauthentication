<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication;

use Zend\Expressive\Authentication\AuthenticationInterface;

/**
 * The configuration provider for the OAuth2ClientAuthentication module.
 */
class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            'aliases' => [
                AuthenticationInterface::class => OAuth2Adapter::class,
            ],
            'factories'  => [
                OAuth2Adapter::class => OAuth2AdapterFactory::class,
                OAuth2CallbackMiddleware::class => OAuth2CallbackMiddlewareFactory::class,
                OAuth2ProviderFactory::class => OAuth2ProviderFactoryFactory::class,
                RedirectResponseFactory::class => RedirectResponseFactoryFactory::class,
                UnauthorizedResponseFactory::class => UnauthorizedResponseFactoryFactory::class,
            ],
        ];
    }

    public function getTemplates() : array
    {
        return [
            'paths' => [
                'oauth2clientauthentication' => [__DIR__ . '/../templates'],
            ],
        ];
    }
}
