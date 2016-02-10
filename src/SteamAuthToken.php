<?php

namespace SteamAuth;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SteamAuthToken extends AbstractToken
{
    public function __construct(SteamAuthUser $user, $roles = array())
    {
        parent::__construct($roles);

        parent::setUser($user);

        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return '';
    }

    public function getUser()
    {
        return parent::getUser();
    }

    public function getSteamId()
    {
        return parent::getUser()->getSteamID();
    }

}
