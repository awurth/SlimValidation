### Download with Composer
``` bash
$ composer require awurth/slim-validation
```

### Configure the validator
To initialize the validator, create a new instance of `Awurth\SlimValidation\Validator`
``` php
Validator::__construct([ bool $showValidationRules = true [, array $defaultMessages = [] ]])
```

#### $showValidationRules
* If set to `true`, errors will be stored in an associative array with the validation rules names as the key
``` php
$errors = [
    'username' => [
        'length' => 'The username must have a length between 8 and 16',
        'alnum' => 'The username must contain only letters (a-z) and digits (0-9)'
    ]
];
```
* If set to `false`, errors will be stored in an array of strings
``` php
$errors = [
    'username' => [
        'The username must have a length between 8 and 16',
        'The username must contain only letters (a-z) and digits (0-9)'
    ]
];
```

#### $defaultMessages
An array of messages to overwrite the default [Respect Validation](https://github.com/Respect/Validation) messages
``` php
$defaultMessages = [
    'length' => 'This field must have a length between {{minValue}} and {{maxValue}} characters',
    'notBlank' => 'This field is required'
];
```

### Add the validator as a service
You can add the validator to the app container to access it easily through your application
``` php
$container['validator'] = function () {
    return new Awurth\SlimValidation\Validator();
};
```
