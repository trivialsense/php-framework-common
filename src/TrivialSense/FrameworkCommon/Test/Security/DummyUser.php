<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Test\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class DummyUser implements UserInterface
{
    protected $roles;

    public function __construct($roles)
    {
        if(!is_array($roles))
            $roles = array($roles);

        $this->roles = $roles;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword()
    {
        return 'test';
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return 'salt';
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return 'test';
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
    }
}
