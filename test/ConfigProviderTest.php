<?php
/**
 * @see       https://github.com/phly/phly-expressive-oauth2clientauthentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/phly/phly-expressive-oauth2clientauthentication/blob/master/LICENSE.md New BSD License
 */

namespace PhlyTest\OAuth2ClientAuthentication;

use PHPUnit\Framework\TestCase;
use Phly\OAuth2ClientAuthentication\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    public function setUp()
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray()
    {
        $config = ($this->provider)();
        $this->assertInternalType('array', $config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);
    }
}
