<?php

namespace SteamAuth;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SteamAuthProvider implements AuthenticationProviderInterface
{

    protected $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        if ($this->userProvider instanceof SteamAuthUserProviderInterface)
            $user = $this->userProvider->loadUserBySteamId($token->getSteamID());
        else
            $user = $this->userProvider->loadUserByUsername($token->getSteamID());

        if ($user)
        {
            $authToken = new SteamAuthToken($user, $user->getRoles());

            return $authToken;
        }

        throw new AuthenticationException("Ooooops SteamAuth problem");
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SteamAuthToken;
    }

}
