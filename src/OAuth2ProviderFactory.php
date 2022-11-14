<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication;

use League\OAuth2\Client\Provider;
use Psr\Container\ContainerInterface;

class OAuth2ProviderFactory
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws Exception\MissingProviderConfigException
     */
    public function createProvider(string $name): Provider\AbstractProvider
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
