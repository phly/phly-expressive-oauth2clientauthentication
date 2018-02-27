<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class RedirectResponseFactoryFactory
{
    public const TEMPLATE = 'oauth2authentication::401';

    public function __invoke(ContainerInterface $container) : callable
    {
        return function (string $url) use ($container) : ResponseInterface {
            $response = $container->get(ResponseInterface::class)();

            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        };
    }
}
