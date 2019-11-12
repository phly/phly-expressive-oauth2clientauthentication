# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.0.0 - 2019-11-12

### Added

- [#3](https://github.com/phly/phly-expressive-oauth2clientauthentication/pull/3) adds support for all League OAuth2 Clients that inherits from the upstream `League\OAuth2\Client\Provider\AbstractProvider`.

- [#3](https://github.com/phly/phly-expressive-oauth2clientauthentication/pull/3) adds the method `forProviderKey(string $provider)` in `MissingProviderConfigException` in order to assert that the provider key has been set for providers in the configuration.

### Changed

- [#3](https://github.com/phly/phly-expressive-oauth2clientauthentication/pull/3) changes array disposition in the configuration files to include `provider` and `options` keys **(BC break)**. The provider array key tells the factory what to instantiate, and the options value is passed to the `Provider` constructor.  Read the documentation on [local/environment-specific configuration](https://phly.github.io/phly-expressive-oauth2clientauthentication/config/) for specific implementation details and examples.

- [#3](https://github.com/phly/phly-expressive-oauth2clientauthentication/pull/3) allows the username to default to `$resourceOwner->getId()` in method `getUsernameFromResourceOwner(ResourceOwnerInterface $resourceOwner) : string` if methods `$resourceOwner->getEmail()` and `$resourceOwner->getNickname()` don't exist, instead of throwing an `UnexpectedResourceOwnerTypeException`.

### Deprecated

- Nothing.

### Removed

- [#3](https://github.com/phly/phly-expressive-oauth2clientauthentication/pull/3) removes `UnsupportedProviderException`, as it is not used anymore.

- [#3](https://github.com/phly/phly-expressive-oauth2clientauthentication/pull/3) removes `UnexpectedResourceOwnerTypeException`, as it is not used anymore.

### Fixed

- [#3](https://github.com/phly/phly-expressive-oauth2clientauthentication/pull/3) fixes a namespace reference within a shipped config file.

## 1.0.1 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0 - 2018-10-18

### Added

- Adds the method `OAuth2User::getDetail(string $name, $default = null)` in
  order to fulfill the zend-expressive-authentication 1.0.0 API for the
  `UserInterface`.

### Changed

- The method `OAuth2User::getUserRoles() : array` was refactored to
  `OAuth2User::getRoles() : iterable` in order to match the
  zend-expressive-authentication 1.0.0 API.

- The method `OAuth2User::getUserData() : array` was refactored to
  `OAuth2User::getDetails() : array` in order to match the
  zend-expressive-authentication 1.0.0 API.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.1 - 2018-03-28

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes how the callback factory produces a pipeline. Instead of using an
  `Application` instance derived from the `ApplicationFactory` (which will
  receive a shared route collector and shared middleware), it now produces a
  `MiddlewarePipe` instance into which it pipes the various middleware. It also
  creates a _new_ router, based on the type returned from the container (it
  assumes no constructor arguments are necessary), and passes that to a new
  `RouteMiddleware` instance to ensure it is sandboxed from the main
  application.

## 0.2.0 - 2018-03-27

### Added

- [#1](https://github.com/phly/phly-expressive-oauth2clientauthentication/pull/1)
  adds support for Expressive v3 and zend-expressive-authentication 0.4+.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#1](https://github.com/phly/phly-expressive-oauth2clientauthentication/pull/1)
  removes support for Expressive v2 releases, including pre-0.4 releases of
  zend-expressive-authentication.

### Fixed

- Nothing.

## 0.1.2 - 2017-11-16

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes an import in the DebugProviderMiddlewareFactory for the
  RedirectResponseFactory to ensure it resolves correctly.

## 0.1.1 - 2017-11-16

### Added

- Adds templates for PlatesPHP, Twig, and zend-view.
- Adds documentation covering templates.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2017-11-16

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
