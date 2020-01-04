# phly-mezzio-oauth2clientauthentication

This library provides a [league/oauth2-client](http://oauth2-client.thephpleague.com)
adapter for use with [mezzio-authentication](https://docs.laminas.dev/mezzio-authentication).
It supports all OAuth2 Clients that inherit from `League\OAuth2\Client\Provider\AbstractProvider`.

## Installation

Install via Composer:

```bash
$ composer require phly/phly-mezzio-oauth2clientauthentication
```

If you are using the [laminas-component-installer Composer
plugin](https://docs.laminas.dev/laminas-component-installer/),
this will automatically register the shipped `ConfigProvider` with your
application, as well as those of its dependencies (including
mezzio-authentication and mezzio-session). If you are not, you
will need to use the shipped
`Phly\Mezzio\OAuth2ClientAuthentication\ConfigProvider` to add configuration
to your application:

```php
use Phly\Mezzio\OAuth2ClientAuthentication\ConfigProvider;

return (new ConfigProvider())();
```

You will also need to install one or more of the OAuth2 providers you wish to
use. As an example:

```bash
$ composer require league/oauth2-instagram league/oauth2-google league/oauth2-facebook
```
