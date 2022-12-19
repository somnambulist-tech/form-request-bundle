# Somnambulist Form Request Bundle

[![GitHub Actions Build Status](https://img.shields.io/github/actions/workflow/status/somnambulist-tech/form-request-bundle/tests.yml?logo=github&branch=main)](https://github.com/somnambulist-tech/form-request-bundle/actions?query=workflow%3Atests)
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

### Property Pass-Through

The following `ParameterBag`s can be accessed via property accessors or method calls:

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

The validation rules use [somnambulist/validation](https://github.com/somnambulist-tech/validation) instead of Symfony's
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

Validated data is stored in the `data` property and is accessible directly from the controller. From
version 1.5.0 this is a `ValidatedDataBag`, previously it was a `ParameterBag`. The data bag supports
dot notation access to keys (with the same restrictions as the main FormRequest) along with `nullOrValue`
and several other methods for keys, values, and filtering by callback.

Alternatively: the original request data can be accessed via property pass-through or by calling
`source()` to get the request object.

### Using the Form Request

In your controller, instead of type-hinting the standard request, type-hint your form request. If
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

`FormRequest`s support dot notation access to get to nested values. This can be used for single values,
to check for deeply nested values easily or to extract a set of values into a value object or set.

For example: to get a single value: `$form->get('user.address.line_1')` would get `line_1` from a user
array that contains an address array.

The behaviour of dot notation has several edge cases:

 * `*` operator is not supported at this time,
 * `only` will return a flattened `ParameterBag` with the dot notation keys
 * `without` will return a `ParameterBag` with the original data without the specified keys

Additionally:

When using dot notation with `only` the result will be similar to `nullOrValue` with `subNull` as false
however it may contain values from the request, query, or files. `nullOrValue` will only work with a
single data source. If you require fine control use `nullOrValue`.

When using dot notation with `without` the result will contain data from request, query, and files. As a
`ParameterBag` is returned, there are no dot accessors.

Optionally:

If you make use of [api-bundle](https://github.com/somnambulist-tech/api-bundle) and document query
arguments that should not be treated as part of the validated data (e.g. page/per_page etc); these
can be excluded from the validated data by overriding the `$ignore` property with the set of key names
that should be excluded from the validated data.

### Authorizing a Form Request

Form requests can have custom authorization checks; however this feature requires that Symfony Security
has been implemented as the `security` service is required.

To add custom auth checks override the `authorize()` method and add the necessary checks.

The `authorize` method receives the current `Security` service that has access to the current user and
the `isGranted()` method.

For example, to require new users can only be made by an Admin user:

```php
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Symfony\Component\Security\Core\Security;

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

Custom validators can be added by creating a new Rule that extends `Somnambulist\Components\Validation\Rule`,
implementing the logic for validation (and any custom messages) and then creating a new service. Rules will be
automatically assigned to the validator using the class name without namespace converted to `snake_case`.
Alternatively individual rules can be tagged with `somnambulist.form_request_bundle.rule` and the attribute
`rule_name` to set a specific alias for the rule:

For example:

```php
<?php
use Somnambulist\Components\Validation\Rule;
use Ramsey\Uuid\Uuid;

class UuidRule extends Rule
{
    protected string $message = 'The :attribute is not a valid UUID or is NIL';

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

See the [documentation](https://github.com/somnambulist-tech/validation) for more details on how to pass arguments,
the available rules, and how to handle translation of messages.

__Note:__ all rules must have unique names and same names will overwrite any pre-existing rules.

__Note:__ several rules are needed internally; if you experience odd behaviour it could be because you changed the
behaviour of a built-in rule.

### Adding missing mime-types

`somnambulist/validation` includes a mime-type guesser that is registered by default and is available as a service.
You can add additional mime-types and extensions to this by injecting the service and configuring it, either in a
boot method or in a service.

Alternatively: the implementation can be replaced entirely provided it implements the `MimeTypeGuesser` interface.

## Tests

PHPUnit 9+ is used for testing. Run tests via `vendor/bin/phpunit`.
