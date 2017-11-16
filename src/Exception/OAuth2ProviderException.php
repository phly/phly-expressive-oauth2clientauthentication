<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication\Exception;

use RuntimeException;
use Throwable;

class OAuth2ProviderException extends RuntimeException implements ExceptionInterface
{
    public static function forErrorString(string $error) : self
    {
        return new self(sprintf(
            'OAuth2 provider raised an error: %s',
            $error
        ), 401);
    }

    public static function forThrowable(Throwable $throwable) : self
    {
        return new self(sprintf(
            'OAuth2 provider raised an error: %s',
            $throwable->getMessage()
        ), 401, $throwable);
    }
}
