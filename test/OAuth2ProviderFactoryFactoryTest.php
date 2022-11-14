<?php

declare(strict_types=1);

namespace PhlyTest\Mezzio\OAuth2ClientAuthentication;

use Phly\Mezzio\OAuth2ClientAuthentication\OAuth2ProviderFactory;
use Phly\Mezzio\OAuth2ClientAuthentication\OAuth2ProviderFactoryFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class OAuth2ProviderFactoryFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testServiceFactoryProducesExpectedFactory()
    {
        $container      = $this->prophesize(ContainerInterface::class)->reveal();
        $serviceFactory = new OAuth2ProviderFactoryFactory();
        $factory        = $serviceFactory($container);

        $reflection          = new ReflectionClass($factory);
        $reflectionContainer = $reflection->getProperty('container');
        $reflectionContainer->setAccessible(true);

        $this->assertInstanceOf(OAuth2ProviderFactory::class, $factory);
        $this->assertSame($container, $reflectionContainer->getValue($factory));
    }
}
