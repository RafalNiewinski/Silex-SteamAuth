<?php

namespace SteamAuth;

use Symfony\Component\Security\Core\User\UserProviderInterface;

interface SteamAuthUserProviderInterface extends UserProviderInterface
{
    public function loadUserBySteamId($steamid);
}
