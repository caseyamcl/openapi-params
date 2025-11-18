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
readonly class ObjectDeserializeStep implements PreparationStep
{
    /**
     * No documentation necessary for this
     */
    public function getApiDocumentation(): ?string
    {
        return null;
    }

    public function __toString(): string
    {
        return 'deserializes value to object according to OpenAPI specs';
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
