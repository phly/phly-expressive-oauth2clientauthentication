<?php

declare(strict_types=1);

namespace PhlyTest\Mezzio\OAuth2ClientAuthentication;

use Phly\Mezzio\OAuth2ClientAuthentication\RedirectResponseFactoryFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class RedirectResponseFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory   = new RedirectResponseFactoryFactory();
    }

    public function testServiceFactoryReturnsCallable()
    {
        $responseFactory = ($this->factory)($this->container->reveal());
        $this->assertIsCallable($responseFactory);
    }

    public function testResponseFactoryReturns302ResponseWithLocationBasedOnUrlArgument()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->withHeader('Location', '/some/url')->will([$response, 'reveal']);
        $response->withStatus(302)->will([$response, 'reveal']);
        $this->container->get(ResponseInterface::class)->willReturn(function () use ($response) {
            return $response->reveal();
        });

        $factory = ($this->factory)($this->container->reveal());
        $result  = $factory('/some/url');
        $this->assertSame($response->reveal(), $result);
    }
}
