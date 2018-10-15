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
