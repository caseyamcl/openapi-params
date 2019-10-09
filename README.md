# Paramee

An OpenApi-compatible parameter processing library

This library aims to provide a cross-framework, reusable framework for
describing and processing parameters compatible with the [OpenApi 3.x](https://swagger.io/specification/)
standard.

It is useful for people who want to create RESTful APIs that are PHP 
code-driven (i.e. spec doesn't come from a YAML or JSON file, but is written into the code itself)
 

## Features:
 
 * Provides a low-level API to describe parameters in an OpenApi3-compatible way
 * Validation via the [`respect/validation` library](https://respect-validation.readthedocs.io/en/1.1/)
 * IDE auto-completion friendly
 * Support for parameter dependencies
 * Bottom-up approach
 * PSR-4/PSR-12 compliant
 * As close to 100% test coverage as is practically achievable
 
## Quick Usage:

```php

use Paramee\Paramee;
use Paramee\PreparationStep\CallbackStep;

// Create an empty parameter list
$queryParams = Paramee::queryParams();

// Add a string
$queryParams->addAlphaNumericValue('test1', '_')
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
$queryParams->addYesNoValue('test4')
    ->setDescription('Boolean parameter');

// Prepared is an instance of Paramee\Model\ParameterValues
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

// Get documentation is an array 
var_dump($queryParams->getApiDocumentation());
```

## In-Depth

### Concepts

The concepts and abstractions in this library are based largely off of the
[OpenApi v3](https://swagger.io/specification/) specification.  There are
a few definitions:

* **Parameter** - A defined ...
* **Format** - A defined...
* **Preparation Step** - (see below)
* **Deserializer** - ...
* **Parameter Validation Rule** - A combination of a [Respect Validation Rule]((https://respect-validation.readthedocs.io/en/1.1/))
  and a description of what the rule does. 

Each parameter runs through a series of "preparation steps" which run in
serial, one after the other.  If everything succeeds, then the prepared
value is returned. This allows you to transform the value into whatever 
the consuming library needs (an entity object, for example).

If a step produces an exception, Paramee bails, and subsequent steps are
not run.

### Using a logger to debug

Paramee supports the use of a PSR-3-compatible logger to 

### Parameter Types and Formats

Paramee supports all of the built-in [OpenApi v3 parameter types](https://swagger.io/docs/specification/data-models/data-types/)
and formats.  Types are in the `Paramee\Type` namespace, and formats are in
the `Paramee\Format` namespace.

In addition, Paramee provides a couple of extra built-in "convenience" formats:

* **AlphanumericFormat** - 
* **CsvFormat**
* **TemporalFormat**
* **UuidFormat**
* **YesNoFormat** 

### Adding custom formats

OpenApi3 doesn't allow you to specify custom data types, but it does allow custom
formats within data types.  Simply implement the `Contract\PreparationStep` interface.

### Validation

A special preparation step is built-in for validation using the 
[`Respect/validation`](https://respect-validation.readthedocs.io/en/1.1/) library.
This is the most popular PHP library for validation.

### Callback Step

_todo: this_

### Other built-in steps

Built-in preparation steps are in the `Paramee\PreparationStep` namespace:

| Step Class                         | What it does                                                      |
| ---------------------------------- | ----------------------------------------------------------------- |
| `AllowNullPreparationStep`         | Allows NULL values if specified in `allowNullable` is `TRUE`      |
| `ArrayDeserializeStep`             | Deserialize an array if there is a deserializer in the context    |
| `ArrayItemsPreparationStep`        | Prepares individual items in an array parameter                   |
| `CallbackStep`                     | Calls a custom callback (see above)                               |
| `DependencyCheckStep`              | If there are [#](parameter dependencies), this step checks them   |
| `EnsureCorrectDataTypeStep`        | Checks if the data matches the expected type, and if typecasting is allowed, attempts to typecast the value |
| `EnumCheckStep`                    | Checks value against a list of allowed values (if specified)      |
| `ObjectDeserializeStep`            | Deserialize an object if there is a deserializer in the context   |
| `PrepareObjectPropertiesStep`      | Prepares individual properties in an object if they are specified |
| `RespectValidationStep`            | Runs built-in validation rules (see above)                        |
| `SanitizeStep`                     | Optionally sanitizes string parameters with `filter_var` (off by default) |

## Handling Errors

Paramee was designed around the assumption that errors would be most
commonly turned into HTTP messages.

_todo.. document in-depth_

## Debugging

Paramee 