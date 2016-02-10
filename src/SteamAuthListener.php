<?php

namespace SteamAuth;

require_once __DIR__.'/openid.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

class SteamAuthListener extends AbstractAuthenticationListener
{
    private $OpenID;
    
    public function __construct(\LightOpenID $openid, \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage, \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface $authenticationManager, \Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface $sessionStrategy, \Symfony\Component\Security\Http\HttpUtils $httpUtils, $providerKey, \Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface $successHandler, \Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface $failureHandler, array $options = array(), \Psr\Log\LoggerInterface $logger = null, \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher = null)
    {
        $this->OpenID = $openid;
        parent::__construct($tokenStorage, $authenticationManager, $sessionStrategy, $httpUtils, $providerKey, $successHandler, $failureHandler, $options, $logger, $dispatcher);
    }

    protected function requiresAuthentication(Request $request)
    {
        return parent::requiresAuthentication($request);
    }

    protected function attemptAuthentication(Request $requset)
    {        
        if($this->OpenID->mode == 'cancel')
        {
            return new RedirectResponse($this->OpenID->authUrl());
        }
        else if($this->OpenID->mode)
        {
            if($this->OpenID->validate())
            {
                $steamid = basename($this->OpenID->identity);
                $user = new SteamAuthUser($steamid);
                $token = new SteamAuthToken($user);
                
                return $this->authenticationManager->authenticate($token);
            }
        }
        
        return new RedirectResponse($this->OpenID->authUrl());
    }

}
