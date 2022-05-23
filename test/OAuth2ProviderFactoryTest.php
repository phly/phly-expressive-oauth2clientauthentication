<?php

declare(strict_types=1);

namespace PhlyTest\Mezzio\OAuth2ClientAuthentication;

use Generator;
use League\OAuth2\Client\Provider;
use Phly\Mezzio\OAuth2ClientAuthentication\Debug\DebugProvider;
use Phly\Mezzio\OAuth2ClientAuthentication\Exception;
use Phly\Mezzio\OAuth2ClientAuthentication\OAuth2ProviderFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class OAuth2ProviderFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var OAuth2ProviderFactory|ObjectProphecy */
    private $factory;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory   = new OAuth2ProviderFactory($this->container->reveal());
    }

    public function invalidConfiguration(): array
    {
        return [
            'empty'                => [[]],
            'missing-provider'     => [['oauth2clientauthentication' => []]],
            'missing-provider-key' => [
                [
                    'oauth2clientauthentication' => [
                        'debug' => [], /*Missing provider key*/
                    ],
                ],
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

    public function validConfiguration(): Generator
    {
        yield 'debug' => [
            'debug',
            [
                'provider' => DebugProvider::class,
                'options'  => [],
            ],
            DebugProvider::class,
        ];

        yield 'github' => [
            'github',
            [
                'provider' => Provider\Github::class,
                'options'  => [
                    'clientId'     => '',
                    'clientSecret' => '',
                    'redirectUri'  => '',
                ],
            ],
            Provider\Github::class,
        ];

        yield 'google' => [
            'google',
            [
                'provider' => Provider\Google::class,
                'options'  => [
                    'clientId'     => '',
                    'clientSecret' => '',
                    'redirectUri'  => '',
                    'hostedDomain' => '',
                ],
            ],
            Provider\Google::class,
        ];

        yield 'custom' => [
            'custom',
            [
                'provider' => Provider\GenericProvider::class,
                'options'  => [
                    'clientId'                => '',
                    'clientSecret'            => '',
                    'redirectUri'             => '',
                    'urlAuthorize'            => '',
                    'urlAccessToken'          => '',
                    'urlResourceOwnerDetails' => '',
                ],
            ],
            Provider\GenericProvider::class,
        ];
    }

    /**
     * @dataProvider validConfiguration
     * @param array $config
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
