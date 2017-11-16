<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication\Exception;

use RuntimeException;

class UnsupportedProviderException extends RuntimeException implements ExceptionInterface
{
    public static function forProvider(string $provider, array $knownProviders) : self
    {
        return new self(sprintf(
            'Unsupported OAuth2 provider "%s"; must be one of: %s',
            $provider,
            implode(', ', $knownProviders)
        ));
    }
}
