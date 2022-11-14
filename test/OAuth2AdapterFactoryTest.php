<?php

declare(strict_types=1);

namespace PhlyTest\Mezzio\OAuth2ClientAuthentication;

use Phly\Mezzio\OAuth2ClientAuthentication\OAuth2Adapter;
use Phly\Mezzio\OAuth2ClientAuthentication\OAuth2AdapterFactory;
use Phly\Mezzio\OAuth2ClientAuthentication\OAuth2ProviderFactory;
use Phly\Mezzio\OAuth2ClientAuthentication\RedirectResponseFactory;
use Phly\Mezzio\OAuth2ClientAuthentication\UnauthorizedResponseFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class OAuth2AdapterFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testServiceFactoryProducesAdapter()
    {
        $providerFactory             = $this->prophesize(OAuth2ProviderFactory::class)->reveal();
        $unauthorizedResponseFactory = function () {
        };
        $redirectResponseFactory     = function () {
        };

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(OAuth2ProviderFactory::class)->willReturn($providerFactory);
        $container->get(UnauthorizedResponseFactory::class)->willReturn($unauthorizedResponseFactory);
        $container->get(RedirectResponseFactory::class)->willReturn($redirectResponseFactory);

        $serviceFactory = new OAuth2AdapterFactory();
        $adapter        = $serviceFactory($container->reveal());

        $reflection                = new ReflectionClass($adapter);
        $reflectionProviderFactory = $reflection->getProperty('providerFactory');
        $reflectionProviderFactory->setAccessible(true);
        $reflectionUnauthorizedResponseFactory = $reflection->getProperty('unauthorizedResponseFactory');
        $reflectionUnauthorizedResponseFactory->setAccessible(true);
        $reflectionRedirectResponseFactory = $reflection->getProperty('redirectResponseFactory');
        $reflectionRedirectResponseFactory->setAccessible(true);

        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertSame($providerFactory, $reflectionProviderFactory->getValue($adapter));
        $this->assertSame($unauthorizedResponseFactory, $reflectionUnauthorizedResponseFactory->getValue($adapter));
        $this->assertSame($redirectResponseFactory, $reflectionRedirectResponseFactory->getValue($adapter));
    }
}
