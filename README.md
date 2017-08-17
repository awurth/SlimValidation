# Slim Validation

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/bdf52753-f379-41c6-85cf-d1d1379b4aa7/mini.png)](https://insight.sensiolabs.com/projects/bdf52753-f379-41c6-85cf-d1d1379b4aa7) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/awurth/slim-validation/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/awurth/slim-validation/?branch=2.x) [![Build Status](https://travis-ci.org/awurth/slim-validation.svg?branch=2.x)](https://travis-ci.org/awurth/slim-validation) [![Total Downloads](https://poser.pugx.org/awurth/slim-validation/downloads)](https://packagist.org/packages/awurth/slim-validation) [![License](https://poser.pugx.org/awurth/slim-validation/license)](https://packagist.org/packages/awurth/slim-validation)

A validator for the Slim PHP Micro-Framework, using [Respect Validation](https://github.com/Respect/Validation)

## Installation
``` bash
$ composer require awurth/slim-validation
```

### Configuration
To initialize the validator, create a new instance of `Awurth\SlimValidation\Validator`
``` php
Validator::__construct([ bool $storeErrorsWithRules = true [, array $defaultMessages = [] ]])
```

##### $storeErrorsWithRules
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

##### $defaultMessages
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

## Usage
``` php
use Respect\Validation\Validator as V;

// The validate method returns the validator instance
$validator = $container->validator->validate($request, [
    'get_or_post_parameter_name' => V::length(6, 25)->alnum('_')->noWhitespace(),
    // ...
]);

if ($validator->isValid()) {
    // Do something...
} else {
    $errors = $validator->getErrors();
}
```

### Custom messages
Slim Validation allows you to set custom messages for validation errors. There are 4 types of custom messages

##### Default rules messages
The ones defined in the `Validator` constructor.
##### Global rules messages
Messages that overwrite **Respect Validation** and **default rules messages** when calling the `validate` method.
##### Individual rules messages
Messages for a single request parameter. Overwrites all above messages.
##### Single parameter messages
Defines a single error message for a request parameter, ignoring the validation rules. Overwrites all messages.

#### Global rules messages
``` php
$container->validator->validate($request, [
    'get_or_post_parameter_name' => V::length(6, 25)->alnum('_')->noWhitespace(),
    // ...
], [
    'length' => 'Custom message',
    'alnum' => 'Custom message',
    // ...
]);
```

#### Individual rules messages
``` php
$container->validator->validate($request, [
    'get_or_post_parameter_name' => [
        'rules' => V::length(6, 25)->alnum('_')->noWhitespace(),
        'messages' => [
            'length' => 'Custom message',
            'alnum' => 'Custom message',
            // ...
        ]
    ],
    // ...
]);
```

#### Single parameter messages
``` php
$container->validator->validate($request, [
    'get_or_post_parameter_name' => [
        'rules' => V::length(6, 25)->alnum('_')->noWhitespace(),
        'message' => 'This field must have a length between 6 and 25 characters and contain only letters and digits'
    ],
    // ...
]);
```

## Twig extension
This package comes with a Twig extension to display error messages and submitted values in your Twig templates. You can skip this step if you don't want to use it.

To use the extension, you must install twig first
``` bash
$ composer require slim/twig-view
```

### Configuration
``` php
$container['view'] = function ($container) {
    // Twig configuration
    $view = new Slim\Views\Twig(...);
    // ...

    // Add the validator extension
    $view->addExtension(
        new Awurth\SlimValidation\ValidatorExtension($container['validator'])
    );

    return $view;
};
```

### Functions
``` twig
{# Use has_errors() function to know if a form contains errors #}
{{ has_errors() }}

{# Use has_error() function to know if a request parameter is invalid #}
{{ has_error('param') }}

{# Use error() function to get the first error of a parameter #}
{{ error('param') }}

{# Use errors() function to get all errors #}
{{ errors() }}

{# Use errors() function with the name of a parameter to get all errors of a parameter #}
{{ errors('param') }}

{# Use val() function to get the value of a parameter #}
{{ val('param') }}
```

## Example
##### AuthController.php
``` php
public function register(Request $request, Response $response)
{
    if ($request->isPost()) {
        $this->validator->validate($request, [
            'username' => V::length(6, 25)->alnum('_')->noWhitespace(),
            'email' => V::notBlank()->email(),
            'password' => V::length(6, 25),
            'confirm_password' => V::equals($request->getParam('password'))
        ]);
        
        if ($this->validator->isValid()) {
            // Register user in database
            
            return $response->withRedirect('url');
        }
    }
    
    return $this->view->render($response, 'register.twig');
}
```

##### register.twig
``` twig
<form action="url" method="POST">
    <input type="text" name="username" value="{{ val('username') }}">
    {% if has_error('username') %}<span>{{ error('username') }}</span>{% endif %}
    
    <input type="text" name="email" value="{{ val('email') }}">
    {% if has_error('email') %}<span>{{ error('email') }}</span>{% endif %}
    
    <input type="text" name="password">
    {% if has_error('password') %}<span>{{ error('password') }}</span>{% endif %}
    
    <input type="text" name="confirm_password">
    {% if has_error('confirm_password') %}<span>{{ error('confirm_password') }}</span>{% endif %}
</form>
```
