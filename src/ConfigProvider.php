<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication;

use Mezzio\Authentication\AuthenticationInterface;

/**
 * The configuration provider for the OAuth2ClientAuthentication module.
 */
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'               => $this->getDependencies(),
            'oauth2clientauthentication' => [],
            'templates'                  => $this->getTemplates(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'aliases'   => [
                AuthenticationInterface::class => OAuth2Adapter::class,
            ],
            'factories' => [
                OAuth2Adapter::class               => OAuth2AdapterFactory::class,
                OAuth2CallbackMiddleware::class    => OAuth2CallbackMiddlewareFactory::class,
                OAuth2ProviderFactory::class       => OAuth2ProviderFactoryFactory::class,
                RedirectResponseFactory::class     => RedirectResponseFactoryFactory::class,
                UnauthorizedResponseFactory::class => UnauthorizedResponseFactoryFactory::class,
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'oauth2clientauthentication' => [__DIR__ . '/../templates'],
            ],
        ];
    }
}
