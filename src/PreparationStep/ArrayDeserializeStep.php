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

use InvalidArgumentException;
use Paramee\Contract\PreparationStepInterface;
use Paramee\Exception\InvalidValueException;
use Paramee\Model\ParameterValues;

/**
 * Class ArrayDeserializeStep
 * @package Paramee\PreparationStep
 */
class ArrayDeserializeStep implements PreparationStepInterface
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
        return 'deserializes value to array';
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
        try {
            return ($deserializer = $allValues->getContext()->getDeserializer())
                ? $deserializer->deserializeArray($value)
                : $value;
        } catch (InvalidArgumentException $e) {
            throw InvalidValueException::fromMessage($this, $paramName, $value, $e->getMessage());
        }
    }
}
