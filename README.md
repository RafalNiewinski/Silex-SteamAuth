##### This version is for Silex ~2.0
###### (version for Silex ~1.3 is available in another branch)
# Silex-SteamAuth
Steam Community authorizaion provider for Silex PHP framework with SecurityServiceProvider.

## Installation
If you want to install with Composer:

```
composer require rafalniewinski/silex-steamauth "~2.0"
```

Alternatively, you can clone this repository:
```
git clone https://github.com/RafalNiewinski/Silex-SteamAuth.git
```


##Usage
This library uses a standard SecurityServiceProvider module. Be sure to read the operating instructions for this module:

http://silex.sensiolabs.org/doc/providers/security.html

Using Steam as authorization provider is similar to "Securing a Path with a Form" paragraph except that the form is replaced with a OpenID and Steam Community mechanism.

###Registering
Use this to register your SteamAuthServiceProvider and core SecurityServiceProvider:
```PHP
$app->register(new SteamAuth\SteamAuthServiceProvider(), array(
    'steam_auth.host' => 'https://domain.tld' // your service domain to configure OpenID
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'admin' => array(
        'pattern' => '^/admin/',
        'steam_auth' => array('check_path' => '/admin/login_check'), // Only this line is different - see below
        'users' => array(
            'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
        ),
    ),
));
```

If you read carefully you'll notice that the registration of SecurityServiceProvider component is the same as securing using form in original Silex tutorial except for one line
**'form' was replaced with 'steam_auth'**.

'check_path' option works the same as in the form authorization (must be defined in secured firewall area)

**IMPORTANT**: Login path is permanently set to /login and must be accessible anonymously or be outside firewall

###Interaction
If you want to sign in user, please redirect him to **/login**

If you want to get Steam ID of signed in user, execute:
```PHP
$app['security.token_storage']->getToken()->getUser()->getSteamID();
```
Be careful because if user isn't signed in `getToken()` or `getUser()` can return `null`

##Defining User Provider
Silex-SteamAuth is compatible with standard UserProviderInterface but you must use user SteamID as Username.

Despite compatibility, it is recommended for convenience use a dedicated SteamAuthUserProviderInterface which includes loadUserBySteamId() method.

Here is a SteamAuth edited simple example of a user provider, where Doctrine DBAL is used to store the users:
```PHP
use SteamAuth\SteamAuthUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\DBAL\Connection;

class UserProvider implements SteamAuthUserProviderInterface
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function loadUserBySteamId($steamid)
    {
        $stmt = $this->conn->executeQuery('SELECT * FROM users WHERE steamid = ?', array($steamid));

        if (!$user = $stmt->fetch())
        {
            //User never previously logged in from this steam accout - probably you should create new account now
            throw new UsernameNotFoundException(sprintf('steamid "%s" does not exist.', $steamid));
        }

        return new SteamAuthUser($user['steamid'], explode(',', $user['Roles']));
    }

    public function loadUserByUsername($steamid)
    {
        //Retain original method operating for compatibility
        return $this->loadUserBySteamId($steamid);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof SteamAuthUser)
        {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserBySteamId($user->getSteamID());
    }

    public function supportsClass($class)
    {
        return $class === 'SteamAuth\SteamAuthUser';
    }

}
```

**Of course you can extend SteamAuthUser class and expand it freely**
