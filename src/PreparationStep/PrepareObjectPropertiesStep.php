<?php

/**
 *  OpenApi-Params Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/openapi-params
 *
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\PreparationStep;

use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Model\ParameterValues;
use OpenApiParams\Type\ObjectParameter;
use stdClass;
use Webmozart\Assert\Assert;

/**
 * Prepare object properties parameter preparation step
 *
 * This recursively traverses the properties of an object until all properties are prepared
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class PrepareObjectPropertiesStep implements PreparationStep
{
    /**
     * @var ObjectParameter
     */
    private $parameter;

    /**
     * PrepareObjectPropertiesStep constructor.
     * @param ObjectParameter $parameter
     */
    public function __construct(ObjectParameter $parameter)
    {
        $this->parameter = $parameter;
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
        return null;
    }

    /**
     * Describe what this step does (will appear in debug log if enabled)
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            'prepare properties in object parameter %s: %s',
            (string) $this->parameter,
            implode(', ', array_keys((array) $this->parameter->getProperties()))
        );
    }

    /**
     * Prepare a parameter
     *
     * @param mixed $value The current value to be processed
     * @param string $paramName
     * @param ParameterValues $allValues All of the values
     * @return mixed
     */
    public function __invoke($value, string $paramName, ParameterValues $allValues)
    {
        Assert::isInstanceOf($value, stdClass::class);

        $errors = [];

        // Clone each defined property, setting the name correctly
        $definedProperties = [];
        foreach ($this->parameter->getProperties() as $property) {
            $definedProperties[$property->getName()] = $property->withName(implode(
                '/',
                [$paramName, $property->getName()]
            ));
        }

        foreach ((array) $value as $propName => $propValue) {
            // No defined property/parameter?  No preparation necessary.
            if (! array_key_exists($propName, $definedProperties)) {
                continue;
            }

            /** @var Parameter $parameter */
            $parameter = $definedProperties[$propName];

            try {
                // Prepare the parameter
                $value->$propName = $parameter->prepare($value->$propName);
            } catch (InvalidValueException $e) {
                // Merge all errors down the stack into a single exception
                $errors = array_merge($errors, $e->getErrors());
            }
        }

        if (! empty($errors)) {
            throw new InvalidValueException($this, $value, $errors);
        }

        return $value;
    }
}
