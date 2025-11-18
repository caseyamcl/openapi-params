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
use OpenApiParams\Model\ParameterValues;

/**
 * Ensure correct data types
 *
 * This step is built into the Parameter class and runs for every value.
 *
 * Its purpose is to ensure that the correct data type was passed (or to type-cast it to
 * the correct type if the ParameterValuesContext allows).
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
readonly class EnsureCorrectDataTypeStep implements PreparationStep
{
    /**
     * EnsureCorrectDataTypeStep constructor.
     * @param array<int,string> $phpDataTypes Built-in PHP data type(s) to check the value against
     * @param bool $allowCast Allow attempt to auto-cast to the given type (useful in numeric query values)
     */
    public function __construct(
        private array $phpDataTypes,
        private bool $allowCast = false
    ) {
    }

    public function getApiDocumentation(): ?string
    {
        // Documentation should be self-explanatory
        return null;
    }

    public function __toString(): string
    {
        return 'ensure correct datatype(s): ' . implode(', ', $this->phpDataTypes);
    }

    public function __invoke(mixed $value, string $paramName, ParameterValues $allValues): mixed
    {
        if (in_array(gettype($value), $this->phpDataTypes)) {
            return $value;
        } elseif ($this->allowCast) {
            foreach ($this->phpDataTypes as $type) {
                if (settype($value, $type)) {
                    return $value;
                }
            }
        }

        $message = sprintf(
            "invalid data type for parameter '%s'; expected: %s; you provided: %s",
            $paramName,
            implode(', ', $this->phpDataTypes),
            gettype($value)
        );
        throw InvalidValueException::fromMessages($this, $paramName, $value, [$message]);
    }
}
