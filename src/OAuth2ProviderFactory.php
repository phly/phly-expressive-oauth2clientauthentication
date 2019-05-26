<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication;

use League\OAuth2\Client\Provider;
use Psr\Container\ContainerInterface;

class OAuth2ProviderFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws Exception\MissingProviderConfigException
     * @param string $name
     * @return Provider\AbstractProvider
     */
    public function createProvider(string $name) : Provider\AbstractProvider
    {
        $config = $this->container->get('config')['oauth2clientauthentication'] ?? [];

        if (! isset($config[$name])) {
            throw Exception\MissingProviderConfigException::forProvider($name);
        }

        if (! isset($config[$name]['provider'])) {
            throw Exception\MissingProviderConfigException::forProviderKey($name);
        }

        $provider = $config[$name]['provider'];

        return new $provider($config[$name]['options']);
    }
}
