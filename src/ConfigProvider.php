<?php
/**
 * @see       https://github.com/phly/phly-expressive-oauth2clientauthentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/phly/phly-expressive-oauth2clientauthentication/blob/master/LICENSE.md New BSD License
 */

namespace Phly\OAuth2ClientAuthentication;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies() : array
    {
        return [
        ];
    }
}
