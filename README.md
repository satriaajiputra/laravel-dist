# Laravel Dist

This package can generate distribution version of your app and ready to distribute on shared hosting.

## Overview

If you has been installed this package, you can execute artisan command of this package

```bash
$ php artisan laravel-dist:run
```

After that, the package will generating the distribution version for you. This is the file that generated using this package.

### Generated files

**dist/public.zip** // this contains the whole of files inside public folder

**dist/laravel.zip** // this contains core of the application, the content inside it can be costumized inside `config/laravel-dist.php` file

## Requirements

- PHP >= 7.2
- PHP ZIP Extension
- allow permission to php commands: `system`, `chdir`, `rmdir`, `unlink`, `mkdir`, `copy`, `closedir`

## Installation

Require this package using composer.

```
composer require satmaxt/laravel-dist
```

Add ServiceProvider to your providers array on `config/app.php` file.

```php
Satmaxt\LaravelDist\ServiceProvider::class
```

If you want to costumize the compresed files inside `laravel.zip`, you can copy the `laravel-dist.php` config file to your `config` folder using this command.

```
php artisan vendor:publish --provider="Satmaxt\LaravelDist\ServiceProvider"
```

## Usage

Generate your distribution app using this command

```
php artisan laravel-dist:run
```

Copyright © 2020. [Satmaxt Developer](https://satmaxt.xyz). Coded with :heart: & :coffee: at Bandung, Indonesia
