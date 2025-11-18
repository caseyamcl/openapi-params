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
 * Prepare an object properties parameter
 *
 * Recursively traverses the properties of an object until all properties are prepared
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class PrepareObjectPropertiesStep implements PreparationStep
{
    /**
     * PrepareObjectPropertiesStep constructor.
     * @param ObjectParameter $parameter
     */
    public function __construct(
        private readonly ObjectParameter $parameter
    ) {
    }

    /**
     * No documentation necessary for this
     */
    public function getApiDocumentation(): ?string
    {
        return null;
    }

    /**
     * Step description
     */
    public function __toString(): string
    {
        return sprintf(
            'prepare properties in object parameter %s: %s',
            $this->parameter,
            implode(', ', array_keys($this->parameter->getProperties()))
        );
    }

    /**
     * Prepare a parameter
     *
     * @param mixed $value The current value to be processed
     * @param string $paramName
     * @param ParameterValues $allValues All the values
     * @return mixed
     */
    public function __invoke(mixed $value, string $paramName, ParameterValues $allValues): mixed
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
            // No defined property/parameter? If not, no preparation is necessary.
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
