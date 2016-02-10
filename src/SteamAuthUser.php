<?php

namespace SteamAuth;

use Symfony\Component\Security\Core\User\UserInterface;

class SteamAuthUser implements UserInterface
{

    protected $steamID;
    protected $roles;

    public function __construct($steamid, array $roles = array())
    {
        $this->steamID = $steamid;
        $this->roles = $roles;
    }

    public function eraseCredentials()
    {
        //HEHE
    }

    public function getPassword()
    {
        return "";
    }

    public function getSalt()
    {
        return "";
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getUsername()
    {
        return $this->steamID;
    }

    public function getSteamID()
    {
        return $this->steamID;
    }

}
