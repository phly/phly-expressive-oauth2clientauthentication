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
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class OAuth2ProviderFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var OAuth2ProviderFactory|ObjectProphecy */
    private $factory;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new OAuth2ProviderFactory($this->container->reveal());
    }

    public function invalidConfiguration()
    {
        return [
            'empty' => [[]],
            'missing-provider' => [['oauth2clientauthentication' => []]],
            'missing-provider-key' => [
                [
                    'oauth2clientauthentication' => [
                        'debug' => [/*Missing provider key*/]
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider invalidConfiguration
     * @param array $config
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
            [
                'provider' => DebugProvider::class,
                'options' => []
            ],
            DebugProvider::class
        ];

        yield 'github' => [
            'github',
            [
                'provider' => Provider\Github::class,
                'options' => [
                    'clientId' => '',
                    'clientSecret' => '',
                    'redirectUri' => '',
                ]
            ],
            Provider\Github::class
        ];

        yield 'google' => [
            'google',
            [
                'provider' => Provider\Google::class,
                'options' => [
                    'clientId' => '',
                    'clientSecret' => '',
                    'redirectUri' => '',
                    'hostedDomain' => '',
                ],
            ],
            Provider\Google::class
        ];

        yield 'custom' => [
            'custom',
            [
                'provider' => Provider\GenericProvider::class,
                'options' => [
                    'clientId' => '',
                    'clientSecret' => '',
                    'redirectUri' => '',
                    'urlAuthorize' => '',
                    'urlAccessToken' => '',
                    'urlResourceOwnerDetails' => '',
                ],
            ],
            Provider\GenericProvider::class
        ];
    }

    /**
     * @dataProvider validConfiguration
     * @param string $providerType
     * @param array $config
     * @param string $expectedType
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
