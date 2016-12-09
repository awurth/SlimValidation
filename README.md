# awurth/slim-validation
A validator for Slim micro-framework

## Installation
```bash
$ composer require awurth/slim-validation
```

## Configuration
You can add the validator to the app container to access it easily through your application
```php
$container['validator'] = function () {
    return new \Awurth\Slim\Validation\Validator();
};
```

## Usage
```
use Respect\Validation\Validator as V;

// This will return the validator instance
$validator = $container->validation->validate($request, [
    'get_or_post_parameter_name' => V::length(6, 25)->alnum('_')->noWhitespace(),
    ...
]);

if ($validator->isValid()) {
    // Do something...
} else {
    $errors = $validator->getErrors();
}
```
