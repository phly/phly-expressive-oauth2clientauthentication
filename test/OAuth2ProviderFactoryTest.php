<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace PhlyTest\Expressive\OAuth2ClientAuthentication;

use League\OAuth2\Client\Provider;
use Phly\Expressive\OAuth2ClientAuthentication\Debug\DebugProvider;
use Phly\Expressive\OAuth2ClientAuthentication\Exception;
use Phly\Expressive\OAuth2ClientAuthentication\OAuth2ProviderFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class OAuth2ProviderFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new OAuth2ProviderFactory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionForUnknownProviderTypes()
    {
        $this->expectException(Exception\UnsupportedProviderException::class);
        $this->factory->createProvider('this-is-unknown');
    }

    public function invalidConfiguration()
    {
        return [
            'empty' => [[]],
            'missing-provider' => [['oauth2clientauthentication' => []]],
        ];
    }

    /**
     * @dataProvider invalidConfiguration
     */
    public function testFactoryRaisesExceptionIfConfigurationNotFoundForProvider(array $config)
    {
        $this->container->get('config')->willReturn($config);

        $this->expectException(Exception\MissingProviderConfigException::class);
        $this->factory->createProvider('debug');
    }

    public function validConfiguration()
    {
        yield 'debug' => [
            'debug',
            [],
            DebugProvider::class
        ];

        yield 'github' => [
            'github',
            [
                'clientId' => '',
                'clientSecret' => '',
                'redirectUri' => '',
            ],
            Provider\Github::class
        ];

        yield 'google' => [
            'google',
            [
                'clientId' => '',
                'clientSecret' => '',
                'redirectUri' => '',
                'hostedDomain' => '',
            ],
            Provider\Google::class
        ];
    }

    /**
     * @dataProvider validConfiguration
     */
    public function testFactoryReturnsOAuth2ClientProviderWithValidConfiguration(
        string $providerType,
        array $config,
        string $expectedType
    ) {
        $this->container->get('config')->willReturn([
            'oauth2clientauthentication' => [
                $providerType => $config,
            ],
        ]);

        $provider = $this->factory->createProvider($providerType);
        $this->assertInstanceOf($expectedType, $provider);
    }
}
