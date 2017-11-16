<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace PhlyTest\Expressive\OAuth2ClientAuthentication;

use Phly\Expressive\OAuth2ClientAuthentication\OAuth2ProviderFactory;
use Phly\Expressive\OAuth2ClientAuthentication\OAuth2ProviderFactoryFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class OAuth2ProviderFactoryFactoryTest extends TestCase
{
    public function testServiceFactoryProducesExpectedFactory()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $serviceFactory = new OAuth2ProviderFactoryFactory();
        $factory = $serviceFactory($container);

        $this->assertInstanceOf(OAuth2ProviderFactory::class, $factory);
        $this->assertAttributeSame($container, 'container', $factory);
    }
}
