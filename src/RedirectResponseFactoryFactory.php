<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class RedirectResponseFactoryFactory
{
    public const TEMPLATE = 'oauth2authentication::401';

    public function __invoke(ContainerInterface $container): callable
    {
        return function (string $url) use ($container): ResponseInterface {
            $response = $container->get(ResponseInterface::class)();

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        };
    }
}
