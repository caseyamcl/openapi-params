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

namespace Paramee\Model;

use ReflectionClass;
use ReflectionException;
use Paramee\Contract\ParamFormat;
use Paramee\Contract\PreparationStep;
use Paramee\Utility\RequireConstantTrait;
use RuntimeException;

/**
 * Abstract Parameter Format
 *
 * Shared logic for all formats
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class AbstractParamFormat implements ParamFormat
{
    use RequireConstantTrait;

    public const TYPE_CLASS = null; // Required
    public const NAME = null;       // Required unless the classname ends with 'Format' (e.g. 'EmailFormat.php')

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
     * If the name is explicitly specified
     * @return string
     * @throws ReflectionException
     */
    public function getName(): string
    {
        if (static::NAME) {
            return (string) static::NAME;
        } elseif ($shortName = (new ReflectionClass($this))->getShortName()) {
            if (substr($shortName, -6) === 'Format') {
                return strtolower(substr($shortName, 0, strlen($shortName) - 6));
            } else {
                throw new RuntimeException(sprintf(
                    'Could not automatically derive format name from class: %s',
                    get_called_class()
                ));
            }
        }
    }

    /**
     * Alias for self::getName()
     *
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->getName();
        } catch (ReflectionException | RuntimeException $e) {
            return '';
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
     * @return array|PreparationStep[]
     */
    public function getPreparationSteps(): array
    {
        // Most formats do not have extra preparation steps.
        return [];
    }
}
