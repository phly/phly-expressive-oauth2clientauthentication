<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication\Exception;

use RuntimeException;

use function sprintf;

class MissingProviderConfigException extends RuntimeException implements ExceptionInterface
{
    public static function forProvider(string $provider): self
    {
        return new self(sprintf(
            'No configuration found for OAuth2 provider "%s"; please provide it via '
            . 'the config key oauth2clientauthentication.%s',
            $provider,
            $provider
        ));
    }

    public static function forProviderKey(string $provider): self
    {
        return new self(sprintf(
            'No provider key found for OAuth2 provider "%s"; please provide it via '
            . 'the config key oauth2clientauthentication.%s.provider',
            $provider,
            $provider
        ));
    }
}
