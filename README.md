# OpenApi-Params

An OpenApi-compatible parameter processing library

This library aims to provide a reusable and framework independent codebase for
describing and processing parameters compatible with the [OpenApi 3.x](https://swagger.io/specification/)
standard.

It is useful for people who want to create RESTful APIs that are PHP 
code-driven (i.e. spec doesn't come from a YAML or JSON file, but is defined
by the code itself).

## Features:
 
 * Provides an OpenApi3-compatible low-level API to define and describe parameters
 * Validation via the [`respect/validation` library](https://respect-validation.readthedocs.io/en/latest/)
 * IDE auto-completion friendly
 * Support for parameter dependencies
 * Bottom-up, code-based approach
 * PSR-4/PSR-12 compliant
 * As close to 100% test coverage as is practically achievable
 
## Quick Usage:

```php

use OpenApiParams\OpenApiParams;
use OpenApiParams\PreparationStep\CallbackStep;

// Create an empty parameter list
$queryParams = OpenApiParams::queryParams();

// Add a string
$queryParams->addAlphaNumeric('test1', '_')
    ->makeRequired()
    ->setDescription('Test parameter')
    ->addPreparationStep(new CallbackStep('strtoupper', 'convert string to uppercase'))
    ->addDependsOn('test2');

// Add an integer
$queryParams->addInteger('test2')
    ->makeRequired()
    ->setDescription('Another test parameter')
    ->setMinimum(5)
    ->setMaximum(10)
    ->addPreparationStep(new CallbackStep('abs', 'Return the absolute value of the item passed'));

// Add a number
$queryParams->addNumber('test3')
    ->makeOptional()
    ->setDescription('A number parameter')
    ->min(10.05)
    ->max(25.35)
    ->setRequireDecimal(true);

// Add a 'yes/no' string
$queryParams->addYesNo('test4')
    ->setDescription('Boolean parameter');

// Prepared is an instance of OpenApi-Params\Model\ParameterValues
$prepared = $queryParams->prepare([
    'test1' => 'ABC_123',
    'test2' => -9,
    'test3' => 15.25,
    'test4' => 'on'
]);

/* $prepared will be:
 *
 * [
 *     'test1' => 'abc_123' (string)
 *     'test2' => 9 (integer)
 *     'test3' => 15.25 (decimal)
 *     'test4' => true (boolean)
 * ]
 */

// The API documentation is an array 
var_dump($queryParams->getApiDocumentation());
```

## In-Depth

### Concepts

The concepts and abstractions in this library are based off of the[OpenApi v3](https://swagger.io/specification/) 
specification. There are a few definitions:

* **Parameter**
    * This is a strictly-defined, basic primitive data type [OpenApi parameter](https://swagger.io/docs/specification/basic-structure/#parameters).
    * A parameter value can appear in the query string, body, cookie, header, or path.
    * In this library, parameter types are represented by classes in the `Type` namespace, and they are fixed (you cannot create custom types):
        * String
        * Boolean
        * Object
        * Array
        * Number (float or double)
        * Integer (int32 or int64)
* **Parameter Value**
    * This library makes a distinction between parameters (definitions) and parameter values.
    * You define parameter definitions and then pass the definitions a series of values. The library
      then checks the values against the definition rules.
* **Format**
    * An [OpenApi format](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.3.md#data-types).
    * In this library, formats are represented by classes in the `Format` namespace, and they are extensible (you can create your own formats).
    * This library includes all the built-in formats defined by the [OpenApi specification](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.3.md#data-types),
     as well as a [few others](#parameter-types-and-formats).
* **Preparation Step**
    * A class that transforms a value in some way (see details below).
    * For example, the `SanitizeStep` sanitizes string parameters.
    * You can add as many custom preparation steps as you need to.
* **Deserializer**
    * A class that [deserializes arrays and objects](https://swagger.io/docs/specification/serialization/)
    * This library includes one deserializer, and it behaves as follows:
        * Path parameters are deserialized with `style=simple`, `explode=true`
        * Query parameters are deserialized with `style=form`, `explode=false`
        * Header parameters are deserialized with `style=simple`, `explode=true`
        * Cookie parameters are deserialized with `style=form`, `explode=false`
        * Body parameters are not deserialized
    * You can define a custom deserializer if the default one doesn't suit your needs
* **Parameter Validation Rule**
    * A combination of a [Respect Validation Rule](https://respect-validation.readthedocs.io/en/latest/) and a description 
      of what the rule does.

## Parameter Types and Formats

OpenApi-Params supports all the built-in [OpenApi v3 parameter types](https://swagger.io/docs/specification/data-models/data-types/)
and formats.  Types are in the `OpenApi-Params\Type` namespace, and formats are in the `OpenApi-Params\Format` namespace.

In addition, OpenApi-Params provides a couple of extra built-in "convenience" formats:

* **AlphanumericFormat** - Accepts and validates alphanumeric values, with optional additional parameters
* **EmailFormat** - Accepts and validates any email address via the 
  [`egulias/email-validator` package](https://packagist.org/packages/egulias/email-validator)
* **CsvFormat** - Accepts and converts to an array any string containing comma-separated
  values (e.g. 'a,b,c').  In addition, you can specify custom delimiters (e.g. 'a|b|c')
* **TemporalFormat** - Accepts and converts to instance of `CarbonImmutable` any
  string supported by PHP's [`strtotime` function](https://www.php.net/manual/en/function.strtotime.php) 
* **UuidFormat** - Accepts and validates any valid UUID
* **YesNoFormat** - Accepts, validates, and converts to boolean any 
  "truthy" string, including 'true/false', '1/0', 'yes/no', or 'on/off'

### Adding custom formats

OpenApi3 doesn't allow you to specify custom data types, but it does allow custom formats for data types.
Simply implement the `Contract\ParamFormat` interface.

Or, for convenience, extend the `Model\AbstractParamFormat` class:

```php
use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Type\StringParameter;

class IpAddressFormat extends AbstractParamFormat
{
    // This is a required constant
    public const TYPE_CLASS = StringParameter::class;
    
    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array|ParameterValidationRule
     */
    public function getValidationRules() : array
    {
         // TODO: Provide example of getValidationRules() method in action
         return [];
    }
}
```

In addition, if the parameter format does something to prepare or transform
the value, override the `getPreparationSteps()` method.

An example of this is in the `Format\CsvFormat`, which deserializes the
value into an array.

Finally, if you want the format to append to the `description` in the API
documentation generated by this library, override the `getDocumentation()` method.
By default, it returns `NULL`, which means to not append anything to the `description`.

For an example of a format that adds a description, refer to the
`Format\Alphanumeric` format (which describes which characters are allowed
in the string).

### Object values

Object values consist of nested Parameter objects. For example, consider the following JSON:API data structure:

```json
{
  "data": {
    "id": "512",
    "type": "people",
    "attributes": {
      "displayName": "Alice Jones",
      "title": "CEO"
    }
  }
}
```

You would configure this structure via the following code:

```php
import OpenApiParams\Type\ObjectParameter;
import OpenApiParams\Type\StringParameter;
use Respect\Validation\Validator as v;

$objParam = new ObjectParameter('data');
$objParam->addProperty((new StringParameter('id'))->addValidationRule(v::numericVal()));
$objParam->addProperty((new StringParameter('people'))->addValidationRule(v::alnum('_')));

$attrParam = (new ObjectParameter('attributes'))
    ->addProperty((new \OpenApiParams\Type\StringParameter('displayName'))->setSanitize(true))
    ->addProperty((new \OpenApiParams\Type\StringParameter('title')));
    
$objParam->addProperty($attrParam);

// Prepare data
$input = json_decode($rawInputValue);
$preparedData = $objParam->prepare($input);
```

This will recursively prepare all parameters defined as part of the object, and return an exception with all the errors
listed that occurred during processing.

## Validation

This library provides a built-in preparation step is for validation using the 
[`Respect/validation`](https://respect-validation.readthedocs.io/en/2.3/) library.

Rules are wrapped in the `ParameterValidationRule` class, because most
rules require documentation (which is the point of the OpenApi specification!).

However, you can add a rule to a parameter without documentation if you
believe that documentation is not necessary (e.g. the validation is self-evident in the parameter format).

```php
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\OpenApiParams;
use Respect\Validation\Validator as v;

$paramList = OpenApiParms::bodyParams();
$pwParam = $paramList->addPassword('password', required: true);

$ruleOne = new ParameterValidationRule(v::length(8, null));
$ruleTwo = v::alnum('_-!#');
$ruleThree = fn($val, $allVals) => $val !== $allVals->get('username')->getRawValue();
$pwParam->addValidationRules($ruleOne, $ruleTwo, $ruleThree);

$prepared = $paramList->prepare(['password' => 'correctHorseBatteryStaple!']);
$prepared->getPreparedValue('password');
```

## Preparation Steps

Each parameter runs through a series of preparation steps which run in serial, one after the other. If everything 
succeeds, then the prepared value is returned. This allows you to transform the value into whatever the consuming 
library needs (an entity object, for example).

If an error or exception occurs during a step, subsequent steps are not run. See the "Handling Errors" section below for
details.

### Other built-in steps

Built-in preparation steps are automatically added for specific types and formats, and are in the 
`OpenApiParams\PreparationStep` namespace. The built-in steps are as follows, in alphabetical order:

| Step Class                    | What it does                                                                                                |
|-------------------------------|-------------------------------------------------------------------------------------------------------------|
| `AllowNullPreparationStep`    | Allows NULL values if specified in `allowNullable` is `TRUE`                                                |
| `ArrayDeserializeStep`        | Deserialize an array if there is a deserializer in the context                                              |
| `ArrayItemsPreparationStep`   | Prepares individual items in an array parameter                                                             |
| `CallbackStep`                | Calls a custom callback (see below)                                                                         |
| `DependencyCheckStep`         | If there are parameter dependencies (e.g., param 'x' allowed only if 'y'), this step checks them            |
| `EnsureCorrectDataTypeStep`   | Checks if the data matches the expected type, and if typecasting is allowed, attempts to typecast the value |
| `EnumCheckStep`               | Checks value against a list of allowed values (if specified)                                                |
| `ObjectDeserializeStep`       | Deserialize an object if there is a deserializer in the context                                             |
| `PrepareObjectPropertiesStep` | Prepares individual properties in an object if they are specified                                           |
| `RespectValidationStep`       | Runs built-in validation rules (see above)                                                                  |
| `SanitizeStep`                | Optionally sanitizes string parameters with `filter_var` (off by default)                                   |

### Callback Step

In addition to the above built-in steps, this library provides for the fairly common use-case of needing to perform a custom action on a parameter.
For example, converting a value into a database entity, processing a filter, or processing pagination info.

To create a custom callback for a parameter

## Handling Errors

OpenApi-Params was designed around the assumption that errors would be most commonly turned into HTTP messages.

_todo.. document in-depth_

## Debugging

OpenApi-Params .. _todo: document in-depth_