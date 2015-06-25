## Laravel Wrapper for OAuth 2.0 Server

This is a [Dingo Laravel OAuth 2.0 Server](https://github.com/dingo/oauth2-server-laravel) fork updated to support Laravel 5. This package allows easier integration for Laravel applications.

[![Build Status](https://travis-ci.org/microweber/oauth2-server-laravel.svg?branch=master)](https://travis-ci.org/microweber/oauth2-server-laravel)

## Installation

The package can be installed with Composer, either by modifying your `composer.json` directly or using the `composer require` command.

```
composer require microweber/oauth2-server-laravel:0.1.*
```

> Note that this package is still under development and has not been tagged as stable.

Make sure you add `Microweber\OAuth2\OAuth2ServiceProvider` to your array of providers in `app/config/app.php`.

## Storage Adapters

This wrapper provides an additional storage adapter that integrates with Laravel's Fluent Query Builder.

- `Microweber\OAuth2\Storage\FluentAdapter`

This adapter is enabled by default in the configuration and will use your default connection from `app/config/database.php`.
