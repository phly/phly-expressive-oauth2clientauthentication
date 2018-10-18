# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0 - TBD

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
