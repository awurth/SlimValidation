# Slim Validation

[![CI](https://github.com/awurth/SlimValidation/actions/workflows/ci.yml/badge.svg)](https://github.com/awurth/SlimValidation/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/awurth/slim-validation/v/stable)](https://packagist.org/packages/awurth/slim-validation)
[![License](https://poser.pugx.org/awurth/slim-validation/license)](https://packagist.org/packages/awurth/slim-validation)

[![Total Downloads](https://poser.pugx.org/awurth/slim-validation/downloads)](https://packagist.org/packages/awurth/slim-validation)
[![Monthly Downloads](http://poser.pugx.org/awurth/slim-validation/d/monthly)](https://packagist.org/packages/awurth/slim-validation)

A validator for PHP, using [Respect Validation](https://github.com/Respect/Validation) (**Requires PHP 8.1+**)

> This project was originally designed to be used with the Micro-Framework "Slim", but can now
  be used with any [psr/http-message](https://github.com/php-fig/http-message)
  compliant framework, or any other PHP project if you don't need 
  request parameters validation.

## Installation

``` bash
$ composer require awurth/slim-validation
```

## Documentation

* [**5.x**](https://github.com/awurth/SlimValidation/tree/5.x/docs) (current, PHP >= 8.1) 
* [**3.4**](https://github.com/awurth/SlimValidation/tree/3.x/docs) (outdated, PHP >= 7.1)

## Usage

The following example shows how to validate that a string is at least 10 characters long:

``` php
use Awurth\Validator\Validator;
use Respect\Validation\Validator as V;

$validator = Validator::create();
$failures = $validator->validate('Too short', V::notBlank()->length(min: 10);

if (0 !== $failures->count()) {
    // Validation failed: display errors
    foreach ($failures as $failure) {
        echo $failure->getMessage();
    }
}
```

The `validate()` method returns a list of validation failures as an object that implements [`ValidationFailureCollectionInterface`](src/ValidationFailureCollectionInterface.php). If you have lots of validation failures, you can filter them with a callback:

``` php
use Awurth\Validator\ValidationFailureInterface;

$failures = $validator->validate(/* ... */);
$filteredFailures = $failures->filter(static function (ValidationFailureInterface $failure, int $index): bool {
    return $failure->getRuleName() === 'notBlank';
});
```

## License

This package is available under the [MIT license](LICENSE).
