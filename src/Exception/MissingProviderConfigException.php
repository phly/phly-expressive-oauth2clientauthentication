<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication\Exception;

use RuntimeException;

class MissingProviderConfigException extends RuntimeException implements ExceptionInterface
{
    public static function forProvider(string $provider) : self
    {
        return new self(sprintf(
            'No configuration found for OAuth2 provider "%s"; please provide it via '
            . 'the config key oauth2clientauthentication.%s',
            $provider,
            $provider
        ));
    }

    public static function forProviderKey(string $provider) : self
    {
        return new self(sprintf(
            'No provider key found for OAuth2 provider "%s"; please provide it via '
            . 'the config key oauth2clientauthentication.%s.provider',
            $provider,
            $provider
        ));
    }
}
