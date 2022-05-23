<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication\Exception;

use RuntimeException;
use Throwable;

use function sprintf;

class OAuth2ProviderException extends RuntimeException implements ExceptionInterface
{
    public static function forErrorString(string $error): self
    {
        return new self(sprintf(
            'OAuth2 provider raised an error: %s',
            $error
        ), 401);
    }

    public static function forThrowable(Throwable $throwable): self
    {
        return new self(sprintf(
            'OAuth2 provider raised an error: %s',
            $throwable->getMessage()
        ), 401, $throwable);
    }
}
