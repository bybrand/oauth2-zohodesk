# Zoho Desk Provider for PHP OAuth 2.0 Client

This package provides Zoho Desk OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

First, you can do get Client ID and Client Secret in "Zoho Developer console". Full documentation, can be see in [Zoho documentation](https://www.zoho.com/developer/).

## Installation

```
composer require bybrand/oauth2-zohodesk
```

## Usage
This is a instruction base to get the token, and in then, to save in your database to future request. The method `getResourceOwner` return your first organization, via `/api/v1/organizations`. See more in Zoho Desk documentation [Get all organizations](https://desk.zoho.com/support/APIDocument.do#Organizations#Organizations_Getallorganizations)

You do not need get `getResourceOwner` if you not need.

```
use Bybrand\OAuth2\Client\Provider\ZohoDesk as ProviderZohoDesk;;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

$params = $_GET;

$provider = new ProviderZohoDesk([
    'clientId'     => 'key-id',
    'clientSecret' => 'secret-key',
    'redirectUri'  => 'your-url-redirect'    
]);

if (!empty($params['error'])) {
    // Got an error, probably user denied access
    $message = 'Got error: ' . htmlspecialchars($params['error'], ENT_QUOTES, 'UTF-8');

    // Return error.
    echo $message;
}
if (!isset($params['code']) or empty($params['code'])) {
    // If we don't have an authorization code then get one
    $authorizationUrl = $provider->getAuthorizationUrl([
        'scope' => [
            'Desk.basic.READ',            
        ]
    ]);

    // Get state and store it to the session
    $_SESSION['oauth2state'] = $provider->getState();

    header('Location: '.$authorizationUrl);
    exit;
// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($params['state']) || ($params['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);

    // Set error and redirect.
    echo 'Invalid stage';
} else {
    try {
        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $params['code']
        ]);

        // Retriave a first Zoho Desk organization.        
        $organization = $provider->getResourceOwner($token);
    } catch (IdentityProviderException $e) {
        // Error, HTTP code Status
    } catch (\Exception $e) {
        // Error, make redirect or message.
    }

    // Save organization data.
    $id   = $organization->getId(),
    $name = $organization->getOrganizationName(),

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```
Please, for more information see the PHP League's general usage examples.

## Refreshing a Token
Zoho Desk token refresh is sent only with `accessType` set to offline. It is important to note that the refresh token is only returned on the first request after this it will be `null`.

You can do revoke access to get the token refresh in a second request. Visit https://accounts.zoho.com and navigate to Connected Apps.

```
$provider = new ProviderZohoDesk([
    'clientId'     => 'key-id',
    'clientSecret' => 'secret-key',
    'redirectUri'  => 'your-url-redirect',
    'accessType'   => 'offline' // Use only for refresh token.
]);

$token = $provider->getAccessToken('authorization_code', [
    'code' => $code
]);

// Persist the token in a database.
$refreshToken = $token->getRefreshToken();
```
See more details in [Generating Access Token From a Refresh Token](https://desk.zoho.com/support/APIDocument.do#Authentication#Using_RefreshToken) Zoho Desk Docs.

## Testing

```
bash
$ ./vendor/bin/phpunit
```

or individual method test, by group.

```
bash
$ ./vendor/bin/phpunit --group=Zoho.GetResourceOwner
```

## License

The MIT License (MIT). Please see [License File](https://github.com/bybrand/oauth2-zoho/blob/master/LICENSE) for more information.
