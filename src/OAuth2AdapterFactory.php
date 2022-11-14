<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication;

use Psr\Container\ContainerInterface;

class OAuth2AdapterFactory
{
    public function __invoke(ContainerInterface $container): OAuth2Adapter
    {
        return new OAuth2Adapter(
            $container->get(OAuth2ProviderFactory::class),
            $container->get(UnauthorizedResponseFactory::class),
            $container->get(RedirectResponseFactory::class)
        );
    }
}
