<?php

namespace SteamAuth;

use Silex\Application;
use Pimple\ServiceProviderInterface;

require_once __DIR__ . '/openid.php';
require_once __DIR__ . '/SteamAuthProvider.php';
require_once __DIR__ . '/SteamAuthListener.php';

class SteamAuthServiceProvider implements ServiceProviderInterface
{

    public function register(\Pimple\Container $app)
    {
        $app['steam_auth'] = function () use ($app)
        {
            $openid = new \LightOpenID($app['steam_auth.host']);
            $openid->identity = 'http://steamcommunity.com/openid';

            return $openid;
        };

        $app['security.authentication_listener.factory.steam_auth'] = $app->protect(function ($name, $options) use ($app)
        {
            $options = array_replace_recursive(array(
                'check_path' => '/login/steamauthcheck'
                    ), $options);

            if (!isset($app['security.authentication.success_handler.' . $name]))
            {
                $app['security.authentication.success_handler.' . $name] = $app['security.authentication.success_handler._proto']($name, $options);
            }
            if (!isset($app['security.authentication.failure_handler.' . $name]))
            {
                $app['security.authentication.failure_handler.' . $name] = $app['security.authentication.failure_handler._proto']($name, $options);
            }

            // define the authentication provider object
            if (!isset($app['security.authentication_provider.' . $name . '.steam_auth']))
            {
                $app['security.authentication_provider.' . $name . '.steam_auth'] = function () use ($app, $name)
                {
                    return new SteamAuthProvider($app['security.user_provider.' . $name]);
                };
            }

            // define the authentication listener object
            if (!isset($app['security.authentication_listener.'.$name.'.steam_auth']))
            {
                $app['security.authentication_listener.'.$name.'.steam_auth'] = function () use ($app, $name, $options) {
                    return new SteamAuthListener(
                        $app['steam_auth'],
                        $app['security.token_storage'],
                        $app['security.authentication_manager'],
                        isset($app['security.session_strategy.'.$name]) ? $app['security.session_strategy.'.$name] : $app['security.session_strategy'],
                        $app['security.http_utils'],
                        $name,
                        $app['security.authentication.success_handler.'.$name],
                        $app['security.authentication.failure_handler.'.$name],
                        $options,
                        $app['logger'],
                        $app['dispatcher']
                    );
                };
            }

            $bindName = "steam_auth_{$name}_";
            $app->match($options['check_path'], function() {})->bind($bindName . 'check');

            $app->match('/login', function () use ($app, $bindName)
            {
                return $app->redirect($app['url_generator']->generate($bindName . 'check'));
            });

            return array(
                //Authentication Provider ID
                'security.authentication_provider.'.$name.'.steam_auth',
                //Authentication Listener ID
                'security.authentication_listener.'.$name.'.steam_auth',
                //Entry Point ID
                null,
                //Position of listener in stack
                'pre_auth'
            );
        });
    }
}
