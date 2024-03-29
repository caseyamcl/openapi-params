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

use InvalidArgumentException;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValues;

/**
 * Class ObjectDeserializeStep
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ObjectDeserializeStep implements PreparationStep
{
    /**
     * Get API Documentation for this step
     *
     * If this step defines a rule that is important to be included in the API description, then include
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
        return 'deserializes value to object';
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
        try {
            return ($deserializer = $allValues->getContext()->getDeserializer())
                ? $deserializer->deserializeObject($value)
                : $value;
        } catch (InvalidArgumentException $e) {
            throw InvalidValueException::fromMessage($this, $paramName, $value, $e->getMessage());
        }
    }
}
