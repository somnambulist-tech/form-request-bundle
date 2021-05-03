# Somnambulist Form Request Bundle

[![GitHub Actions Build Status](https://img.shields.io/github/workflow/status/somnambulist-tech/form-request-bundle/tests?logo=github)](https://github.com/somnambulist-tech/form-request-bundle/actions?query=workflow%3Atests)
[![Issues](https://img.shields.io/github/issues/somnambulist-tech/form-request-bundle?logo=github)](https://github.com/somnambulist-tech/form-request-bundle/issues)
[![License](https://img.shields.io/github/license/somnambulist-tech/form-request-bundle?logo=github)](https://github.com/somnambulist-tech/form-request-bundle/blob/master/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/somnambulist/form-request-bundle?logo=php&logoColor=white)](https://packagist.org/packages/somnambulist/form-request-bundle)
[![Current Version](https://img.shields.io/packagist/v/somnambulist/form-request-bundle?logo=packagist&logoColor=white)](https://packagist.org/packages/somnambulist/form-request-bundle)

An implementation of form requests from Laravel for Symfony based on the original work by
[Adam Sapraliev](https://github.com/adamsafr/form-request-bundle).

## Requirements

 * PHP 8.0+
 * symfony/framework-bundle

## Installation

Install using composer, or checkout / pull the files from github.com.

 * composer require somnambulist/form-request-bundle

## Usage

Add the `SomnambulistFormRequestBundle` to your `bundles.php` list and add a config file in `packages`
if you wish to configure the bundle. The following options can be set:

```yaml
somnambulist_form_request:
    subscribers:
        authorization: true
        form_validation: true
```

`authorization` registers an event subscriber that will convert `AccessDeniedExceptions` to a JSON
response.
`form_validation` registers an event subscriber that will convert `FormValidationException` to a
JSON response including the fields that failed validation and the rules and message that failed.

__Note:__ the subscribers are enabled by default.

### Custom Rules

This package includes overrides for the following rules:

 * required - validates a field is required supporting Symfony UploadedFile files
 * uploaded_file - validates an uploaded file supporting Symfony UploadedFile files
 * mimes - validates an uploaded file mime-type is one of the given extensions

The following are additional rules:

 * uuid - validates a UUID is valid and not NIL (all 0/zeros)
 * string - validates that the value is a string via `is_string`
 * prohibited - the field is prohibited always
 * prohibited_if - the field is prohibited if, another field has a value
 * prohibited_unless - the field is prohibited, unless the field has a value

Example usage for prohibited:

```php
// the time is not allowed if the date is false or 0
$rules = [
    'date' => 'date',
    'time' => 'prohibited_if:date,false,0'
];

// last is not allowed if first is not Bob
$rules = [
    'first' => 'required|string',
    'last' => 'prohibited_unless:first,Bob'
];
```

### Property Pass-Through

The following `ParameterBag`s can be accessed via property accessors:

 * attributes
 * cookies
 * files
 * headers
 * query
 * request
 * server

In addition, the following properties are also available:

 * content
 * session

### Making a Form Request

To make a form request, extend the base `Somnambulist\Bundles\FormRequestBundle\Http\FormRequest` class
and override the `rules()` method to add your own validation rules. The `rules` method has access to the
current request via the `source` property so rules can be constructed using request information.

The validation rules use [rakit/validation](https://github.com/rakit/validation) instead of Symfony's
validation component to allow for easier setup and extension.

For example: to make a form request specifically for validating the data to make a new user you could
create the following:

```php
<?php
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

class NewUserFormRequest extends FormRequest
{
    public function rules() : array
    {
        return [
            'name'     => 'required|min:8|max:100',
            'email'    => 'required|min:4|max:255|email',
            'password' => 'required|min:8|max:100',
        ];
    }
}
```

Validated data is stored in the `data` property and is accessible directly from the controller.

Alternatively: the original request data can be accessed via property pass-through or by calling
`source()` to get the request object.

### Using the Form Request

In your controller, instead of type-hinting the standard request type-hint your form request. If
validation succeeds, then the data in the request will be suitable for the controller to use.

For example:

```php
<?php
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CreateUserController extends AbstractController
{

    public function __invoke(NewUserFormRequest $form)
    {
        // handle request
        $form->data()->get('name');
    }
}
```

### Authorizing a Form Request

Form requests can have custom authorization checks; however this feature requires that Symfony Security
has been implemented as the `security` service is required.

To add custom auth checks override the `authorize()` method and add whatever checks are needed.

The `authorize` method receives the current `Security` service that has access to the current user and
the `isGranted()` method.

For example, to require new users are made by an Admin user:

```php
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;use Symfony\Component\Security\Core\Security;

class NewUserFormRequest extends FormRequest
{
    public function authorize(Security $security) : bool
    {
        return $security->isGranted('ROLE_ADMIN');
    }

    public function rules() : array
    {
        return [
            'name'     => 'required|min:8|max:100',
            'email'    => 'required|min:4|max:255|email',
            'password' => 'required|min:8|max:100',
        ];
    }
}
```

### Adding Validator Rules

Custom validators can be added by creating a new Rule that extends `Rakit\Validation\Rule`, implementing
the logic for validation (and any custom messages) and then creating a new service. Rules will be
automatically assigned to the validator using the class name without namespace converted to `snake_case`.
Alternatively individual rules can tagged with `somnambulist.form_request_bundle.rule` and the attribute
`rule_name` to set a specific alias for the rule:

For example:

```php
<?php
use Rakit\Validation\Rule;
use Ramsey\Uuid\Uuid;

class UuidRule extends Rule
{

    protected $message = 'The :attribute is not a valid UUID or is NIL';

    public function check($value): bool
    {
        return Uuid::isValid($value) && $value !== Uuid::NIL;
    }
}
```

```yaml
services:
    App\Rules\UuidRule:
        tags:
            - { name: 'somnambulist.form_request_bundle.rule', rule_name: 'uuid' }

```

Without the tag, this rule would be registered as: `uuid_rule`.

As rules are registered as services you can use other services for database or API checks etc.

See the `rakit` documentation for more details on how to pass arguments and the available rules.

__Note:__ all rules must have unique names and same names will overwrite any pre-existing

## Tests

PHPUnit 9+ is used for testing. Run tests via `vendor/bin/phpunit`.
