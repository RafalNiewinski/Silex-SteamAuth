<?php

namespace SteamAuth;

interface SteamAuthUserProviderInterface
{
    function loadUserBySteamId($steamid);
}
