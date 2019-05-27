# phly-expressive-oauth2clientauthentication

This library provides a [league/oauth2-client](http://oauth2-client.thephpleague.com)
adapter for use with [zend-expressive-authentication](https://docs.zendframework.com/zend-expressive-authentication).
It supports all OAuth2 Clients that inherits from `League\OAuth2\Client\Provider\AbstractProvider`.

## Installation

Install via Composer:

```bash
$ composer require phly/phly-expressive-oauth2clientauthentication
```

If you are using the [zend-component-installer Composer
plugin](https://docs.zendframework.com/zend-component-installer/),
this will automatically register the shipped `ConfigProvider` with your
application, as well as those of its dependencies (including
zend-expressive-authentication and zend-expressive-session). If you are not, you
will need to use the shipped
`Phly\Expressive\OAuth2ClientAuthentication\ConfigProvider` to add configuration
to your application:

```php
use Phly\Expressive\OAuth2ClientAuthentication\ConfigProvider;

return (new ConfigProvider())();
```

You will also need to install one or more of the OAuth2 providers you wish to
use. As an example:

```bash
$ composer require league/oauth2-instagram league/oauth2-google league/oauth2-facebook
```
