## Validator

### __construct( [ $showValidationRules [, $defaultMessages ]] )
Creates a new Validator instance.

#### Arguments
* *(Optional)* `$showValidationRules` **bool**
    *default* **true**

    * If set to `true`, errors will be stored in an associative array with the validation rules names as the key
    * If set to `false`, errors will be stored in an array of strings

* *(Optional)* `$defaultMessages` **array**
    *default* **array()**

    An array of messages to overwrite the default [Respect Validation](https://github.com/Respect/Validation) messages. See [setDefaultMessages()](#setDefaultMessages-messages)

#### Usage
``` php
// With the default configuration
$validator = new Validator();

// With custom default messages
$validator = new Validator(true, [
    'length' => 'Custom message for "length" rule',
    'notBlank' => 'Custom message for "notBlank" rule'
]);
```

### validate( $input, $rules [, $group [, $messages ]] )
Validates request parameters or an object's properties or an array with the given validation rules.

#### Arguments
* `$input` **Psr\Http\Message\ServerRequestInterface | object | array**

    The object you want to validate

* `$rules` **array**

    An array of validation rules associated to request parameters, an object's properties or an array's keys

* *(Optional)* `$group` **string**
    *default* **null**

    A group name to sort errors

* *(Optional)* `$messages` **array**
    *default* **array()**

    An array of messages to overwrite [Respect Validation](https://github.com/Respect/Validation) and [default rules messages](index.html#defaultMessages) for the given validation rules

#### Return *Validator*

#### Usage
See [Slim Validation Usage](usage.html).

### request( $request, $rules [, $group [, $messages ]] )
Validates request parameters with the given validation rules.

#### Arguments
* `$request` **Psr\Http\Message\ServerRequestInterface**

    The `$request` object from your route callback

See [validate()](#validate-input-rules-group-messages).

#### Return *Validator*

### object( $object, $rules [, $group [, $messages ]] )
Validates an object's properties with the given validation rules.

#### Arguments
* `$object` **object**

    The object you want to validate

See [validate()](#validate-input-rules-group-messages).

#### Return *Validator*

### array( $array, $rules [, $group [, $messages ]] )
Validates an array with the given validation rules.

#### Arguments
* `$array` **array**

    The array you want to validate

See [validate()](#validate-input-rules-group-messages).

#### Return *Validator*

### value( $value, $rules, $key [, $group [, $messages ]] )
Validates a variable with the given validation rules.

#### Arguments
* `$value` **mixed**

    The value you want to validate

* `$rules` **array**

    The validation rules to use to validate the given value

* *(Optional)* `$group` **string**
    *default* **null**

    A group name to sort errors

* *(Optional)* `$messages` **array**
    *default* **array()**

    An array of messages to overwrite [Respect Validation](https://github.com/Respect/Validation) and [default rules messages](index.html#defaultMessages) for the given validation rules

#### Return *Validator*

### isValid( )
Tells if there is no error.

#### Return *bool*

### count( )
Gets the error count.

#### Return *int*

### addError( $key, $message [, $group ] )
Adds an error message.

#### Arguments
* `$key` **string**

    The key of the parameter

* `$message` **string**

    The error message

* *(Optional)* `$group` **string**

    The group name of the error

#### Return *Validator*

### getError( $key [, $index [, $group ] ] )
Gets a list of errors, or all errors if no parameter is provided.

#### Arguments
* `$key` **string**

    The key of the parameter of which you want to get the error

* *(Optional)* `$index` **string**
    *default* **null**

    The index of the error. Can be the name of a validation rule or an `integer` if you set the `showValidationRules` option to `false`.
    If omitted, the method will return the first error.

* *(Optional)* `$group` **string**
    *default* **null**

    The group name of the error

#### Return *string*

### getErrors( [ $key [, $group ] ] )
Gets a list of errors, or all errors if no parameter is provided.

#### Arguments
* *(Optional)* `$key` **string**
    *default* **null**

    The key of the parameter of which you want to get the list of errors

* *(Optional)* `$group` **string**
    *default* **null**

    The group name of the errors

#### Return *array*

### getFirstError( $key [, $group ] )
Gets the first error of a request parameter.

#### Arguments
* `$key` **string**

    The key of the parameter of which you want to get the first error

* *(Optional)* `$group` **string**
    *default* **null**

    The group name of the error

#### Return *string*

### getShowValidationRules( )
Gets the errors storage mode.

#### Return *bool*

### getValue( $key [, $group ] )
Gets a value from the validated data.

#### Arguments
* `$key` **string**

    The key of the parameter of which you want to get the value

* *(Optional)* `$group` **string**
    *default* **null**

    The group name of the parameter

#### Return *mixed*

### getValues( )
Gets the validated data.

#### Return *array*

### removeErrors( [ $key [, $group ]] )
Removes errors.

#### Arguments
* *(Optional)* `$key` **string**
    *default* **null**

    The key of the parameter of which you want to remove the errors

* *(Optional)* `$group` **string**
    *default* **null**

    The group name of the parameter

#### Return *Validator*

### setDefaultMessage( $rule, $message )
Sets default error messages.

#### Arguments
* `$rule` **string**

    The validation rule name

* `$message` **string**

    The error message

#### Return *Validator*

### setDefaultMessages( $messages )
Sets default error messages.

#### Arguments
* `$messages` **array**

    An array of error messages associated to validation rules names
    ``` php
    $messages = [
        'length' => 'This field must have a length between {{minValue}} and {{maxValue}} characters',
        'notBlank' => 'This field is required'
    ];
    ```

#### Return *Validator*

### setErrors( $errors [, $key [, $group ]] )
Sets errors.

#### Arguments
* `$errors` **array**

    An array of error messages
    ``` php
    $errors = [
        'username' => [
            'length' => 'Message for "length" rule',
            'alnum' => 'Message for "alnum" rule'
        ],
        'password' => [
            'Bad password'
        ]
    ];
    ```

* *(Optional)* `$key` **string**
    *default* **null**

    The key of the parameter of which you want to set the errors

* *(Optional)* `$group` **string**
    *default* **null**

    The group name of the parameter

#### Return *Validator*

### setShowValidationRules( $showValidationRules )
Sets the errors storage mode.

#### Arguments
* `$showValidationRules` **bool**

    * If set to `true`, errors will be stored in an associative array with the validation rules names as the key
    * If set to `false`, errors will be stored in an array of strings

#### Return *Validator*

### setValue( $key, $value [, $group ] )
Sets the value of a parameter.

#### Arguments
* `$key` **string**

    The key of the parameter of which you want to set the value

* `$value` **mixed**

    The value of the parameter

* *(Optional)* `$group` **string**

    The group name of the parameter

#### Return *Validator*

### setValues( $values )
Sets values of validated data.

#### Arguments
* `$values` **array**

    An associative array with the request parameters names as the keys and their value as the values
    ``` php
        $validator->setValues([
            'username' => 'awurth',
            'password' => 'my_password'
        ]);
    ```

#### Return *Validator*

## Twig Extension

### has_errors( )
Tells if the validator contains errors.

#### Return *bool*

### has_error( param )
Tells if there is an error message for the given request parameter.

#### Arguments
* `param` **string**

    The request parameter's name

#### Return *bool*

### error( param )
Gets the first error message for the given request parameter.

#### Arguments
* `param` **string**

    The request parameter's name

#### Return *string*

### errors( [ param ] )
Gets all errors.

#### Arguments
* *(Optional)* `param` **string**

    The request parameter's name. If specified, the function will only return errors of this parameter

#### Return *array*

### val( param )
Gets the value of a request parameter in validated data. Can be used to set the `value=""` html attribute after submitting a form that contains errors.

#### Arguments
* `param` **string**

    The request parameter's name

#### Return *mixed*
