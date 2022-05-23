<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication;

use Psr\Container\ContainerInterface;

class OAuth2ProviderFactoryFactory
{
    public function __invoke(ContainerInterface $container): OAuth2ProviderFactory
    {
        return new OAuth2ProviderFactory($container);
    }
}
