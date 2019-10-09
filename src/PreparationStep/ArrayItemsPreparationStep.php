<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\PreparationStep;

use RuntimeException;
use Paramee\Contract\PreparationStep;
use Paramee\Exception\InvalidValueException;
use Paramee\Model\Parameter;
use Paramee\Model\ParameterValues;
use Paramee\ParamTypes;
use Webmozart\Assert\Assert;

/**
 * Class ArrayItemsPreparationStep
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ArrayItemsPreparationStep implements PreparationStep
{
    public const ALL = [];

    /**
     * @var array|Parameter[]
     */
    private $parameterTypeMap;

    /**
     * @var iterable|PreparationStep[]
     */
    private $forEach;

    /**
     * ArrayItemsPreparationStep constructor.
     *
     * @param array $parameterTypeMap keys are PHP data type name; values are an array of possible parameter definitions
     * @param iterable $forEach  Additional rules to run for each item
     */
    public function __construct(array $parameterTypeMap = self::ALL, iterable $forEach = [])
    {
        $this->parameterTypeMap = $parameterTypeMap;
        $this->forEach = $forEach;
    }

    /**
     * Get API Documentation for this step
     *
     * If this step defines a rule that is important to be included in the API documentation, then include
     * it here.  e.g. "value must be ..."
     *
     * @return string|null
     */
    public function getApiDocumentation(): ?string
    {
        return null; // no extra documentation needed
    }

    /**
     * Describe what this step does (will appear in debug log if enabled)
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'prepares each item in an array parameter';
    }

    /**
     * Prepare array parameter
     *
     * @param array $value The current value to be processed
     * @param string $paramName
     * @param ParameterValues $allValues All of the values
     * @return mixed
     */
    public function __invoke($value, string $paramName, ParameterValues $allValues)
    {
        Assert::isArray($value);

        foreach ($value as $idx => $item) {
            try {
                $itemName = implode('/', [$paramName, (string) $idx]);
                $value[$idx] = $this->prepareItem($item, $itemName, $this->resolveParamsForItem($item, $itemName));
            } catch (InvalidValueException $e) {
                $exceptions[$idx] = $e;
            }
        }

        if (! empty($exceptions ?? [])) {
            throw $this->generateException($exceptions ?? [], $value);
        }

        return $value;
    }

    /**
     * Prepare each item in the array
     *
     * @param mixed $value      The value
     * @param string $itemName  Pointer to index (e.g. 'myparam/5' or 'myparam/6', etc..)
     * @param array|Parameter[] $paramTypeMapping
     * @return mixed
     */
    protected function prepareItem($value, string $itemName, array $paramTypeMapping)
    {
        // Go through each mapping and the first one that works wins..
        foreach ($paramTypeMapping as $param) {
            try {
                return $param->prepare($value);
            } catch (InvalidValueException $e) {
                if (count($paramTypeMapping) === 1) {
                    throw $e;
                }
            }
        }

        // If made it here, then we throw an ambiguous exception, because none of the types worked.
        throw InvalidValueException::fromMessage($this, $itemName, $value, sprintf(
            "Invalid parameter (type mis-match or type constraints failed: %s)",
            implode(', ', $this->resolveValidParameterTypeNames($paramTypeMapping))
        ));
    }

    /**
     * @param array|InvalidValueException[] $exceptions All of the exceptions that occurred;
     *                                                  keys are the respective indexes that failed.
     * @param $value
     * @return InvalidValueException
     */
    private function generateException(array $exceptions, $value)
    {
        $errors = [];
        foreach ($exceptions as $idx => $exception) {
            $errors = array_merge($errors, $exception->getErrors());
        }

        return new InvalidValueException($this, $value, $errors);
    }

    /**
     * Ensure a given value in the array can be mapped to a particular parameter
     *
     * @param mixed $item
     * @param string $paramName
     * @return array|Parameter[]  Empty array for any type allowed
     */
    private function resolveParamsForItem($item, string $paramName): array
    {
        if ($this->parameterTypeMap === self::ALL) {
            // If no parameters explicitly defined for this array, then guess it.

            try {
                $params = [ParamTypes::resolveParameterForValue($item, $paramName)];
            } catch (RuntimeException $e) {
                $msg = sprintf('Invalid data type: %s (could not map to parameter)', gettype($item));
                throw InvalidValueException::fromMessage($this, $paramName, $item, $msg);
            }
        } elseif (array_key_exists(gettype($item), $this->parameterTypeMap)) {
            // If parameters are explicitly defined for this type, then use those
            $params = $this->parameterTypeMap[gettype($item)];
        } elseif (count($this->listParameters()) === 1 && current($this->listParameters())->allowsTypeCast()) {
            //If no parameters are explicitly defined for this type, but there is only one type and it allows
            // typecast
            $params = $this->listParameters();
        } else {
            // Craft an error message
            $validTypes = [];
            foreach ($this->parameterTypeMap as $phpType => $paramTypes) {
                $validTypes = array_merge($validTypes, $this->resolveValidParameterTypeNames($paramTypes));
            }

            $message = sprintf('Invalid data type: %s (allowed: %s)', gettype($item), implode(', ', $validTypes));
            throw InvalidValueException::fromMessage($this, $paramName, $item, $message);
        }

        // Set name and append additional preparation steps for each parameter
        return array_map(function (Parameter $param) use ($paramName) {
            $param = $param->withName($paramName);
            call_user_func_array([$param, 'addPreparationStep'], $this->forEach);
            return $param;
        }, $params);
    }

    /**
     * Resolve parameter type names
     *
     * @param array|Parameter[] $validParams
     * @return array|string[]
     */
    private function resolveValidParameterTypeNames(array $validParams): array
    {
        return array_unique(array_map(function (Parameter $parameter) {
            return $parameter->getName()
                ? sprintf("%s (%s)", $parameter->getTypeName(), $parameter->getName())
                : $parameter->getTypeName();
        }, $validParams));
    }

    /**
     * @return array|Parameter[]
     */
    private function listParameters(): array
    {
        $out = [];

        foreach ($this->parameterTypeMap as $type => $params) {
            $out = array_merge($out, $params);
        }

        return $out ?? [];
    }
}
