Change Log
==========

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
