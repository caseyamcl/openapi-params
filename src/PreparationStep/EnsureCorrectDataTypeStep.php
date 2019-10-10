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
 * Ensure Correct Data Type step
 *
 * This step is built into the AbstractParameter, so if your parameter extends
 * that class, it will be run automatically.
 *
 * Its purpose is to ensure that the correct data type was passed (or to type-cast it to
 * the correct type if the ParameterValuesContext allows)
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class EnsureCorrectDataTypeStep implements PreparationStep
{
    /**
     * @var string
     */
    private $phpDataTypes;

    /**
     * @var bool
     */
    private $allowCast;

    /**
     * EnsureCorrectDataTypeStep constructor.
     * @param array|string[] $phpDataTypes  Built-in PHP data type to check for
     * @param bool $allowCast      Attempt to auto-cast to the given type (useful in numeric query values)
     */
    public function __construct(array $phpDataTypes, bool $allowCast = false)
    {
        $this->phpDataTypes = $phpDataTypes;
        $this->allowCast = $allowCast;
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
        // Documentation should be self explanatory...
        return null;
    }

    /**
     * Describe what this step does
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'ensure correct datatype(s): ' . implode(', ', $this->phpDataTypes);
    }

    /**
     * Prepare a parameter
     *
     * @param mixed $value
     * @param string $paramName
     * @param ParameterValues $allAllValues
     * @return mixed
     */
    public function __invoke($value, string $paramName, ParameterValues $allAllValues)
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
            'invalid data type; expected: %s; you provided: %s',
            implode(', ', $this->phpDataTypes),
            gettype($value)
        );
        throw InvalidValueException::fromMessages($this, $paramName, $value, [$message]);
    }
}
