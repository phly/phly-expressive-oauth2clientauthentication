<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication;

use Zend\Expressive\Authentication\UserInterface;

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

    public function getIdentity() : string
    {
        return $this->identity;
    }

    public function getUserRoles() : array
    {
        return $this->userData['roles'] ?? [];
    }

    public function getUserData() : array
    {
        return $this->userData;
    }
}
