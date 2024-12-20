Change Log
==========

2024-11-27
----------
 
 * fix PHP 8.4 deprecations

2024-06-12
----------
 
 * add additional suggested return types

2024-02-24
----------
 
 * require Symfony 6.4+
 * add Support for Symfony 7
 * update all deprecations

2023-05-09
----------

 * add `payload` to FormRequest for SF 6.3

2023-01-16
----------

 * additional return types for event subscribers

2022-07-26
----------

 * remove unnecessary docblock comments

2022-03-29
----------

 * add `getInt` and `getFloat` to `ValidatedDataBag`

2022-03-11
----------

 * fix only pass valid data to `ValidatedDataBag`, not all including invalid data

2022-02-01
----------

 * add `filterNulls()` to `ValidatedDataBag` to complement `prune()`

2022-01-11
----------

 * fix validation error message handling not outputting the actual error messages

2021-12-14
----------

 * replace `rakit/validation` library with `somnambulist/validation`
   __Note:__ this is a BC break as the API is substantially different
 * allow Symfony 6.0+
 
2021-11-12
----------

 * fix force float value to string in `preg_match()` to avoid type errors

2021-11-04
----------

 * add `ignore` to `FormRequest` to allow excluding keys from the validated data array

2021-10-06
----------

 * add `prune()` to `ValidatedDataBag` to remove empty values
 * add `value()` to `ValidatedDataBag` to return default if the key exists but is empty
 * add additional check to `GetNullOrValue` to check if the array values are empty or not

2021-10-04
----------

 * add `ValidatedDataBag` to hold validated data replacing the previous `ParameterBag`
 * add `__call` to `FormRequest` for same items as `__get`
 * refactor internals to make array helpers global functions
 * dropped final from `nullOrValue` on `FormRequest` to allow overriding in certain cases
 * require Symfony 5.3+

2021-08-02
----------

 * fix `has` to not check attributes and also check files
 * fix `all` to fetch from query, request, files; same as `has` order

2021-07-26
----------

 * add support for dot notation access on:
   * `get`
   * `has`
   * `only`
   * `without`
   * `nullOrValue`
   `only` will return a flattened array using the dot key names
   `without` returns the original data structure in a ParameterBag instance
 * default can now be a callback function

2021-05-26
----------

 * fix type issue with UUID rule and fix issue where rule fails if value is empty/null

2021-04-16
----------

 * add float rule to test for floating point numbers
 * add `nullOrValue` to `FormRequest` to get many values as an array or object or null
 * add `without` to `FormRequest` to get all parameters except those specified

2021-03-29
----------

 * disable humanised keys by default to preserve the input/error mapping
 * add prohibited/if/unless based on the same named Laravel validation rules
 * add mimes replacement to work with Symfony file array

2021-02-05
----------

 * add priority to event subscribers to ensure form-validation runs before api-bundle

2021-01-28
----------

 * add tests for various use cases
 * add file upload tests
 * replacement rules to work with Symfony file array / UploadedFile

2021-01-25
----------

Initial commit with ideas from https://github.com/adamsafr/form-request-bundle
