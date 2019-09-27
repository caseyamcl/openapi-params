# Paramee

An OpenApi-compatible parameter processing library

This library aims to provide a cross-framework, reusable framework for
describing and processing parameters compatible with [OpenApi 3.x](https://swagger.io/specification/).

## Features:
 
 * Provides a low-level API to describe parameters in an OpenApi3-compatible way
 * Validation via the [`respect/validation` library](https://respect-validation.readthedocs.io/en/1.1/)
 * Bottom-up approach
 * PSR-4/PSR-2 compliant
 * 100% test coverage
 
## Quick Usage:

```php

use Paramee\PreparationStep\CallbackStep;

// Create an empty parameter list
$queryParams = Paramee::queryParams();

// Add a string
$queryParams->addString('test1')
    ->makeRequired()
    ->setDescription('Test parameter')
    ->makeAlphanumeric('_'); // make alphanumeric 

$queryParams->addInteger('test2')
    ->makeRequired()
    ->setDescription('Another test parameter')
    ->min(5)
    ->max(10)
    ->addPreparationStep(new CallbackStep(function ($value) {
        return abs($value);
    }, 'Return the absolute value of the item passed'));

$queryParams->addNumber('test3')
    ->makeOptional()
    ->setDescription('A number parameter')
    ->min(10.05)
    ->max(25.35)
    ->setRequireDecimal(true);

$queryParams->addString('test4')
    ->makeYesNo()
    ->setDescription('Boolean parameter');

// Prepared is an instance of Paramee\Model\ParameterValues
$prepared = $queryParams->prepare([
    'test1' => 'abc_123',
    'test2' => 9,
    'test3' => 15.25,
    'test4' => 'true'
]);

// Get documentation is an array 
var_dump($queryParams->getApiDocumentation());
```