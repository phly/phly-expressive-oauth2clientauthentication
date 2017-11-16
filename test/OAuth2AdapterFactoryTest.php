<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace PhlyTest\Expressive\OAuth2ClientAuthentication;

use Phly\Expressive\OAuth2ClientAuthentication\OAuth2Adapter;
use Phly\Expressive\OAuth2ClientAuthentication\OAuth2AdapterFactory;
use Phly\Expressive\OAuth2ClientAuthentication\OAuth2ProviderFactory;
use Phly\Expressive\OAuth2ClientAuthentication\RedirectResponseFactory;
use Phly\Expressive\OAuth2ClientAuthentication\UnauthorizedResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class OAuth2AdapterFactoryTest extends TestCase
{
    public function testServiceFactoryProducesAdapter()
    {
        $providerFactory = $this->prophesize(OAuth2ProviderFactory::class)->reveal();
        $unauthorizedResponseFactory = function () {
        };
        $redirectResponseFactory = function () {
        };

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(OAuth2ProviderFactory::class)->willReturn($providerFactory);
        $container->get(UnauthorizedResponseFactory::class)->willReturn($unauthorizedResponseFactory);
        $container->get(RedirectResponseFactory::class)->willReturn($redirectResponseFactory);

        $serviceFactory = new OAuth2AdapterFactory();
        $adapter = $serviceFactory($container->reveal());

        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertAttributeSame($providerFactory, 'providerFactory', $adapter);
        $this->assertAttributeSame($unauthorizedResponseFactory, 'unauthorizedResponseFactory', $adapter);
        $this->assertAttributeSame($redirectResponseFactory, 'redirectResponseFactory', $adapter);
    }
}
