# Customs API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/customs-api.svg?style=flat-square)](https://packagist.org/packages/javaabu/customs-api)
[![Test Status](../../actions/workflows/run-tests.yml/badge.svg)](../../actions/workflows/run-tests.yml)
![Code Coverage Badge](./.github/coverage.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/javaabu/customs-api.svg?style=flat-square)](https://packagist.org/packages/javaabu/customs-api)

PHP SDK for interacting with the Maldives [Customs Service API](https://api.customs.gov.mv/)

## Contents

- [Installation](#installation)
    - [Setting up the Customs API credentials](#setting-up-the-customs-api-credentials)
- [Usage](#usage)
    - [Available Methods](#available-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)
- [Disclaimer](#disclaimer)

## Installation

You can install the package via composer:

``` bash
composer require javaabu/customs-api
```

**Laravel 5.5** and above uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

After updating composer, add the ServiceProvider to the providers array in config/app.php

```php
// config/app.php
'providers' => [
    ...
    Javaabu\Customs\CustomsServiceProvider::class,
],
```

Optionally add the facade.
```php
// config/app.php
'aliases' => [
    ...
    'Customs' => Javaabu\Customs\Facades\Customs::class,
],
```

### Setting up the Customs API credentials

Add your Customs Username, Password, and Url (optional) to your `config/services.php`:

```php
// config/services.php
...
'customs' => [
    'username' => env('CUSTOMS_USERNAME'), // Customs API username 
    'password' => env('CUSTOMS_PASSWORD'), // Customs API password 
    'url' => env('CUSTOMS_API_URL'), // optional, use only if you need to override the default,
                                  // defaults to https://api.customs.gov.mv/api/
],
...
```

## Usage

Using the App container:


``` php
$customs = App::make('customs');
$entity = $customs->getTraderByMedNumber('C-0933/2017');
```

Using the Facade

``` php
use Customs;

$entity = Customs::getTraderByMedNumber('C-0933/2017');
```

### Available Methods

``` php
Customs::getTraderByMedNumber($business_registration_number);
Customs::getTraderByCNumber($impoter_exporter_number);
```  

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email info@javaabu.com instead of using the issue tracker.

## Credits

- [Javaabu Pvt. Ltd.](https://github.com/javaabu)
- [Arushad Ahmed (@dash8x)](http://arushad.org)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


## Disclaimer

This package is not in any way officially affiliated with Maldives Customs Service.
The "Customs" name has been used under fair use.

