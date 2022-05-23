<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication;

use Mezzio\Authentication\UserInterface;

class OAuth2User implements UserInterface
{
    /** @var string */
    private $identity;

    /** @var array */
    private $userData;

    public function __construct(string $identity, array $userData)
    {
        $this->identity = $identity;
        $this->userData = $userData;
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function getRoles(): iterable
    {
        return $this->userData['roles'] ?? [];
    }

    /**
     * @param ?string $default - default detail
     */
    public function getDetail(string $name, $default = null): ?string
    {
        return $this->userData[$name] ?? $default;
    }

    public function getDetails(): array
    {
        return $this->userData;
    }
}
