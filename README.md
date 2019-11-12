# phly-expressive-oauth2clientauthentication

[![Build Status](https://secure.travis-ci.org/phly/phly-expressive-oauth2clientauthentication.svg?branch=master)](https://secure.travis-ci.org/phly/phly-expressive-oauth2clientauthentication)
[![Coverage Status](https://coveralls.io/repos/github/phly/phly-expressive-oauth2clientauthentication/badge.svg?branch=master)](https://coveralls.io/github/phly/phly-expressive-oauth2clientauthentication?branch=master)

This library provides a [league/oauth2-client](http://oauth2-client.thephpleague.com)
adapter for use with [zend-expressive-authentication](https://docs.zendframework.com/zend-expressive-authentication).

## Installation

Run the following to install this library:

```bash
$ composer require phly/phly-expressive-oauth2clientauthentication
```

You will also need to install one or more of the OAuth2 providers you wish to
use. As an example:

```bash
$ composer require league/oauth2-instagram league/oauth2-google league/oauth2-facebook
```

## Documentation

Documentation is [in the doc tree](docs/book/), and can be compiled using [mkdocs](http://www.mkdocs.org):

```bash
$ mkdocs build
```

You may also [browse the documentation online](https://phly.github.io/phly-expressive-oauth2clientauthentication/).
