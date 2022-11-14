# phly-mezzio-oauth2clientauthentication

[![Build Status](https://secure.travis-ci.org/phly/phly-mezzio-oauth2clientauthentication.svg?branch=master)](https://secure.travis-ci.org/phly/phly-mezzio-oauth2clientauthentication)
[![Coverage Status](https://coveralls.io/repos/github/phly/phly-mezzio-oauth2clientauthentication/badge.svg?branch=master)](https://coveralls.io/github/phly/phly-mezzio-oauth2clientauthentication?branch=master)

This library provides a [league/oauth2-client](http://oauth2-client.thephpleague.com)
adapter for use with [mezzio-authentication](https://docs.mezzio.dev/mezzio-authentication).

## Installation

Run the following to install this library:

```bash
$ composer require phly/phly-mezzio-oauth2clientauthentication
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

You may also [browse the documentation online](https://phly.github.io/phly-mezzio-oauth2clientauthentication/).
