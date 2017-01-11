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
```php
use Respect\Validation\Validator as V;

// This will return the validator instance
$validator = $container->validation->validate($request, [
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
```php
$container->validation->validate($request, [
    'get_or_post_parameter_name' => V::length(6, 25)->alnum('_')->noWhitespace(),
    // ...
], [
    'length' => 'Custom message',
    'alnum' => 'Custom message',
    // ...
]);
```

## Twig extension
```twig
{# Use has_errors() function to know if a form contains errors #}
{{ has_errors() }}

{# Use has_error() function to know if a request parameter is invalid #}
{{ has_error('param') }}

{# Use error() function to get the first error of a parameter #}
{{ error('param') }}

{# Use errors() function to get all errors of a parameter #}
{{ errors('param') }}

{# Use val() function to get the value of a parameter #}
{{ val('param') }}
```

## Example
```php
{# AuthController.php #}

public function register(Request $request, Response $response)
{
    if ($request->isPost()) {
        $this->validator->validate($request, [
            'username' => V::length(6, 25)->alnum('_')->noWhitespace(),
            'email' => V::notBlank()->email(),
            'password' => V::length(6, 25),
            'confirm-password' => V::equals($request->getParam('password'))
        ]);
        
        if ($this->validator->isValid()) {
            // Register user in database
            
            return $response->withRedirect('url');
        }
    }
    
    return $this->view->render($response, 'register.twig');
}
```

```twig
{# register.twig #}

<form action="url" method="POST">
    <input type="text" name="username" value="{{ val('username') }}">
    {% if has_error('username') %}<span>{{ error('username') }}</span>{% endif %}
    
    <input type="text" name="email" value="{{ val('email') }}">
    {% if has_error('email') %}<span>{{ error('email') }}</span>{% endif %}
    
    <input type="text" name="password" value="{{ val('password') }}">
    {% if has_error('password') %}<span>{{ error('password') }}</span>{% endif %}
    
    <input type="text" name="confirm-password" value="{{ val('confirm-password') }}">
    {% if has_error('confirm-password') %}<span>{{ error('confirm-password') }}</span>{% endif %}
</form>
```
