<?php

declare(strict_types=1);

namespace PhlyTest\Mezzio\OAuth2ClientAuthentication\Debug;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Phly\Mezzio\OAuth2ClientAuthentication\Debug\DebugProvider;
use Phly\Mezzio\OAuth2ClientAuthentication\Debug\DebugResourceOwner;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DebugProviderTest extends TestCase
{
    use ProphecyTrait;

    public function testDefaultState()
    {
        $provider = new DebugProvider();
        $this->assertInstanceOf(AbstractProvider::class, $provider);
        $this->assertSame(DebugProvider::AUTHORIZATION_URL, $provider->getAuthorizationUrl());
        $this->assertSame(DebugProvider::STATE, $provider->getState());

        $token = $provider->getAccessToken('authentication');
        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertSame(DebugProvider::TOKEN, $token->getToken());

        $owner = $provider->getResourceOwner($token);
        $this->assertInstanceOf(DebugResourceOwner::class, $owner);

        $this->assertNull($provider->getBaseAuthorizationUrl());
        $this->assertNull($provider->getBaseAccessTokenUrl([]));
        $this->assertNull($provider->getResourceOwnerDetailsUrl($token));
    }

    public function testAllowsProvidingAuthorizationUrlViaConstructor()
    {
        $url      = '/oauth2/debug/validate';
        $provider = new DebugProvider([
            'authorization_url' => $url,
        ]);
        $this->assertSame($url, $provider->getAuthorizationUrl());
    }
}
