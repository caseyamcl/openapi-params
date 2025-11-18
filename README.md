# OpenApi-Params

An OpenApi-compatible parameter processing library

This library aims to provide a reusable and framework-independent codebase for describing and processing parameters 
compatible with the [OpenApi 3.0.4](https://spec.openapis.org/oas/v3.0.4.html) standard.

It is useful for people who want to create RESTful APIs that are PHP 
[code-first](https://learn.openapis.org/best-practices.html#use-a-design-first-approach) 
(i.e., spec doesn't come from a YAML or JSON file, but is defined by the code itself).

## Features:
 
 * Provides an OpenApi3-compatible low-level API to define and describe parameters
 * Validation via the [Symfony Validator component](https://symfony.com/doc/current/validation.html)
 * IDE auto-completion friendly
 * Support for parameter dependencies
 * Bottom-up, code-based approach
 * PSR-4/PSR-12 compliant
 * Close to 100% test coverage
 
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
    'test2' => 5,
    'test3' => 15.25,
    'test4' => 'on'
])->getPreparedValues();

/* Output will be an array:
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

The concepts and abstractions in this library are based off of the [OpenApi v3.0 specification](https://spec.openapis.org/oas/v3.0.4.html#format).
There are a few definitions:

* **Parameter (definition)**
    * This is a strictly defined, basic primitive data type [OpenApi parameter](https://swagger.io/docs/specification/v3_0/describing-parameters/).
    * Parameter types are objects of classes in the `Type` namespace.
    * Parameter definition types are [strictly defined](https://spec.openapis.org/oas/v3.0.4.html#data-types) (meaning you cannot create custom types) as follows:
        * String
        * Boolean
        * Object
        * Array
        * Number (float or double)
        * Integer (int32 or int64)
* **Parameter Value**
    * The actual values that are defined by parameter definitions; for example, parameter `name=bob`
      * The parameter definition is `name`, and the value is `bob`.
    * Each parameter is an object of a class in the `Type` namespace. 
    * This library checks the values against the definition rules, custom validation rules, and processes them via
      preparation steps (see below).
* **Format**
    * An [OpenApi format](https://spec.openapis.org/oas/v3.0.4.html#format).
    * Formats are represented by classes in the `Format` namespace, and you can create your own formats.
    * This library includes all the built-in formats defined by the [OpenApi specification](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.3.md#data-types),
      as well as a [few others](#parameter-types-and-formats).
* **Preparation Step**
    * A class that transforms a parameter value in some way. For example, the `SanitizeStep` sanitizes string parameters.
    * In addition to the built-in steps that are documented below, you can add as many custom preparation steps as you need.
* **Deserializer**
    * A class that [deserializes arrays and objects](https://swagger.io/docs/specification/serialization/)
    * This library includes one deserializer, and it behaves as follows:
        * Path parameters are deserialized with `style=simple`, `explode=true`.
        * Query parameters are deserialized with `style=form`, `explode=false`.
        * Header parameters are deserialized with `style=simple`, `explode=true`.
        * Cookie parameters are deserialized with `style=form`, `explode=false`.
        * Body parameters are not deserialized.
    * You can define a custom deserializer if the built-in one doesn't suit your needs.
* **Parameter Validation Rule**
    * A wrapper around a [Symfony Constraint](https://symfony.com/doc/current/validation.html#constraints)
      that adds a description of what the rule does for OpenAPI documentation purposes.
    * Implemented as a Preparation Step

#### Parameter Types and Formats

OpenApi-Params supports all the built-in [OpenApi v3 parameter types](https://swagger.io/docs/specification/v3_0/data-models/data-types/)
and formats. Types are in the `OpenApiParams\Type` namespace, and formats are in the `OpenApi-Params\Format` namespace.

In addition, this library provides some extra built-in "convenience" formats:

* **AlphanumericFormat** - Accepts and validates alphanumeric values, with optional additional parameters
* **EmailFormat** - Accepts and validates any email address via the 
  [`egulias/email-validator` package](https://packagist.org/packages/egulias/email-validator)
* **TemporalFormat** - Accepts and converts to instance of `CarbonImmutable` any
  string supported by PHP's [`strtotime` function](https://www.php.net/manual/en/function.strtotime.php) 
* **UuidFormat** - Accepts and validates any valid UUID
* **YesNoFormat** - Accepts, validates, and converts to boolean any "truthy" string, including 'true/false', '1/0', 'yes/no', or 'on/off'

#### Adding custom formats

OpenApi3 does not allow you to specify custom data types, but it does allow custom formats for data types.
To do this, implement the `Contract\ParamFormat` interface.

Or, for convenience, extend the `Model\AbstractParamFormat` class, which provides sensible default implementations:

```php
use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Type\StringParameter;
use Symfony\Component\Validator\Constraints\Ip

class IpAddressFormat extends AbstractParamFormat
{
    // This is a required constant when extending the AbstractParamFormat class
    public const TYPE_CLASS = StringParameter::class;
    
    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically.
     * Sometimes, formats do not need validation rules. In this case, return an empty array.
     *
     * @return array<ParameterValidationRule>
     */
    public function getValidationRules() : array
    {
         return [
            new ParameterValidationRule(new Ip(version: 'all_no_priv'), 'value must be a valid public IP address') 
        ];
    }
}
```

In addition, if the parameter format does something to prepare or transform the value, override the `getPreparationSteps()` method.
By default, it returns an empty array, which means that it does not require any additional preparation steps.
An example of this is in the `Format\CsvFormat`, which deserializes the value into an array.

If you need the format to append the `description` in the OpenAPI documentation, override the `getDocumentation()` method. 
By default, it returns `null`, which means that it does not append anything to the `description`. An example of a format that 
adds a description is the `Format\Alphanumeric` format, which describes which characters are allowed in the string.

### Object values

Object values consist of nested `Parameter` objects. For example, consider the following [json:api](https://jsonapi.org/) data structure:

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

You would configure this structure with the following code:

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

This will recursively prepare all parameters defined as part of the object.
In case the values are erroneous, it will return an exception with all the errors listed that occurred during processing.
see more details in the [Handling Errors](#handling-errors) section.

## Validation

This library provides a built-in preparation step is for validation using the 
[Symfony Validator Component](https://symfony.com/doc/current/validation.html) library.

Rules are wrapped in the `ParameterValidationRule` class, because most rules require OpenApi documentation.

```php
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\OpenApiParams;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\PasswordStrength;

$paramList = OpenApiParms::bodyParams();
$pwParam = $paramList->addPassword('password', required: true);

$ruleOne = new Length(min: 8);
$ruleTwo = new Regex('[/A-Za-z0-9_-!#]+/');
$ruleThree = new PasswordStrength(minScore: 3)
$pwParam->addValidationRules($ruleOne, $ruleTwo, $ruleThree);

$prepared = $paramList->prepare(['password' => 'correctHorseBatteryStaple!']);
$prepared->getPreparedValue('password');
```

In the above example, `$ruleOne` would be better implemented using the built-in `$pwParam->setMinLength(8);`.
`$ruleTwo` is necessary if the format is `password`, because a parameter cannot have two formats assigned. Otherwise,
the built-in `$pwParam->setFormat(new AlphanmericFormat(extraChars: '_-!#'))` would be a better fit.

## Preparation Steps

Each parameter runs through a series of preparation steps that run in serial, one after the other. 
If everything succeeds, then the prepared value is returned.
Built-in steps run automatically for certain types. For example, the `ObjectDeserializeStep` runs for every `Object` parameter type.
You can add your own custom preparation steps by calling the `addPreparationStep()` on the parameter. For example,
a value may be a database ID, and your application needs the actual database record (see the example in the [callback step](#callback-step) section).

If an error or exception occurs during any step, subsequent steps are not run, and an `InvalidValueException` is thrown,
which is caught in the `ParameterList` class logic and compiled into an `AggregateErrorsException` instance.
For details, see the[Handling Errors](#handling-errors) section.

### Other built-in steps

Built-in preparation steps are automatically executed for certain types and formats. They are in the 
`OpenApiParams\PreparationStep` namespace. The built-in steps are as follows, in alphabetical order:

| Step Class                    | What it does                                                                                                    |
|-------------------------------|-----------------------------------------------------------------------------------------------------------------|
| `AllowNullPreparationStep`    | Allows NULL values if specified in `allowNullable` is `TRUE`                                                    |
| `ArrayDeserializeStep`        | Deserialize an array if there is a deserializer in the context                                                  |
| `ArrayItemsPreparationStep`   | Prepares individual items in an array parameter                                                                 |
| `CallbackStep`                | Calls a custom callback (see below)                                                                             |
| `DependencyCheckStep`         | If there are parameter dependencies (e.g., param 'x' allowed only if 'y'), this step checks them                |
| `EnsureCorrectDataTypeStep`   | Checks if the data matches the expected type, and if typecasting is enabled, attempts to typecast the value     |
| `EnumCheckStep`               | Checks value against a list of allowed values (if specified)                                                    |
| `ObjectDeserializeStep`       | Deserialize an object if there is a deserializer in the context                                                 |
| `PrepareObjectPropertiesStep` | Prepares individual properties in an object if they are specified                                               |
| `ValidationStep`              | Runs built-in validation rules; see [Validation](#validation) section                                           |
| `SanitizeStep`                | Optionally sanitizes string parameters; strips slashes and quotes, tags, and runs FILTER_SANITIZE_SPECIAL_CHARS |

Note that any custom steps added with `addPreparationStep` are run after all the built-in steps are run.

### Callback Step

In addition to the built-in steps, this library provides for the fairly common use-case of needing to perform a custom action on a parameter value; 
for example, converting a value into a database entity, processing a filter, or processing pagination info.

The following code demonstrates how to create a custom callback preparation step and assign it to a parameter:

```php
use InvalidArgumentException;
use OpenApiParams\PreparationStep\CallbackStep;
use OpenApiParams\OpenApiParams;

$entityManager = SomeEntityManagerFactory::build();

// This is our callback
$convertToEntityCallback = function (string $value) use ($entityManager): EntityObject {
    $entity = $entityManager->find($value);
    if (! $entity) {
        throw new InvalidArgumentException('Could not resolve ID to entity class: ' . $value);
    }
    return $entity;
};

// Setup a parameter
$paramList = OpenApiParms::bodyParams();
$pwParam = $paramList->addString('entity_id', required: true)->setFormat(new UuidFormat());

// Build CallbackStep
$pwParam->addPreparationStep(new CallbackStep(
    callback: $convertToEntityCallback,
    descripton: 'Converts IDs to database entities',
    documentation: null // Optionally add OpenAPI documentation if it is relevant
));
```

## Parameter processing order and dependencies

This library provides the ability to manually specify the order in which parameters are processed via dependencies
and explicit control of the order in which parameters run.

### Parameter dependencies

You can make a parameter (e.g., `foo`) dependent on another parameter (e.g., `bar`) using the `addDependsOn` method:

```php
use OpenApiParams\OpenApiParams;

$paramList = OpenApiParms::queryParams();
$fooParam = $paramList->addString('foo');
$barParam = $paramList->addString('bar')->addDependsOn('foo');

// 'foo' must be included in the query string if 'bar' is present 
```

Note that if you make the `bar` parameter required (`$barParam->makeRequired()`), the processing will fail if both
`foo` and `bar` are not provided.

If you do not want to make parameters dependent on other parameters, but still control the order they are processed, you can
use `addProcessAfter`:

```php
use OpenApiParams\OpenApiParams;

$paramList = OpenApiParms::queryParams();
$fooParam = $paramList->addString('foo');
$barParam = $paramList->addString('bar')->addProcessAfter('foo');

// 'bar' will be processed after 'foo' if 'foo' is present
```

You can specify an optional callback as the second argument for both the `addDependsOn` and `addProcessAfter` methods. 
The callback signature is `fn(OpenApiParams\Model\ParameterValue $value): void`, the `$value` argument being the dependency.
An `InvalidArgumentException` exception will be caught and processed in the preparation step; other exceptions will bubble up.

```php
use OpenApiParams\OpenApiParams;
use OpenApiParams\Model\ParameterValue;
use InvalidArgumentException;

$cb = function (ParameterValue $pv): void {
    if ($pv !== 'FOO') {
        throw new InvalidArgumentException('foo must equal FOO');
    }
}

$paramList = OpenApiParms::queryParams();
$fooParam = $paramList->addString('foo');
$barParam = $paramList->addString('bar')->addDependsOn('foo', $cb);

// 'foo' must be present, and the value must equal 'FOO' if 'bar' if also present 
```

You can also make a parameter value depend on the absence of another parameter value (`XOR` logic) 
by using the `addDependsOnAbsenceOf` method:

```php
use OpenApiParams\OpenApiParams;

$paramList = OpenApiParms::queryParams();
$fooParam = $paramList->addString('foo');
$barParam = $paramList->addString('bar')->addDependsOnAbsenceOf('foo');

// 'bar' is only allowed if 'foo' is NOT present
```

## Handling Errors

This library was designed around the assumption that errors would be most commonly turned into HTTP messages.



## Debugging

OpenApiParams .. _todo: document in-depth_