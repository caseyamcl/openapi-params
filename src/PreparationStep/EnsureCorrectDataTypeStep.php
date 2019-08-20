<?php
/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\PreparationStep;

use Paramee\Contract\PreparationStepInterface;
use Paramee\Exception\InvalidParameterException;
use Paramee\Model\ParameterValues;

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
class EnsureCorrectDataTypeStep implements PreparationStepInterface
{
    /**
     * @var string
     */
    private $phpDataType;

    /**
     * @var bool
     */
    private $allowCast;

    /**
     * EnsureCorrectDataTypeStep constructor.
     * @param string $phpDataType  Built-in PHP data type to check for
     * @param bool $allowCast      Attempt to auto-cast to the given type (useful in numeric query values)
     */
    public function __construct(string $phpDataType, bool $allowCast = false)
    {
        $this->phpDataType = $phpDataType;
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
        return 'ensure correct datatype';
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
        if (gettype($value) === $this->phpDataType) {
            return $value;
        } else {
            if ($this->allowCast && settype($value, $this->phpDataType)) {
                return $value;
            } else {
                $message = sprintf(
                    'invalid data type; expected: %s; you provided: %s',
                    $this->phpDataType,
                    gettype($value)
                );
                throw InvalidParameterException::fromMessages($this, $paramName, $value, [$message]);
            }
        }
    }
}
