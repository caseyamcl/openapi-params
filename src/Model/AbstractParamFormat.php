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

namespace Paramee\Model;

use ReflectionClass;
use ReflectionException;
use Paramee\Contract\ParamFormatInterface;
use Paramee\Contract\PreparationStepInterface;
use Paramee\Utility\RequireConstantTrait;

/**
 * Abstract Parameter Format
 *
 * Shared logic for all formats
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class AbstractParamFormat implements ParamFormatInterface
{
    const TYPE_CLASS = null;
    const NAME = null;

    use RequireConstantTrait;

    /**
     * Return the type class that this format can be applied to
     *
     * @return string
     */
    public function appliesToType(): string
    {
        return $this->requireConstant('TYPE_CLASS');
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (static::NAME) {
            return (string) static::NAME;
        } else {
            try {
                $shortName = (new ReflectionClass($this))->getShortName();
                return substr($shortName, -6) === 'Format'
                    ? strtolower(substr($shortName, 0, strlen($shortName) - 6))
                    : '';
            } catch (ReflectionException $e) {
                return '';
            }
        }
    }

    public function getDocumentation(): ?string
    {
        // most formats don't add anything the description
        return null;
    }


    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array|ParameterValidationRule[]
     */
    abstract public function getValidationRules(): array;

    /**
     * Get built-in parameter preparation steps
     *
     * These run after validation but before any custom preparation steps
     *
     * @return array|PreparationStepInterface[]
     */
    public function getPreparationSteps(): array
    {
        // Most formats do not have extra preparation steps.
        return [];
    }
}
