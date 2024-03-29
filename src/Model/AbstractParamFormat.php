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

namespace OpenApiParams\Model;

use ReflectionClass;
use ReflectionException;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Utility\RequireConstantTrait;
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
     *
     * @return string
     * @throws RuntimeException
     */
    public function getName(): string
    {
        if (static::NAME) {
            return (string) static::NAME;
        } elseif ($shortName = (new ReflectionClass($this))->getShortName()) {
            if (str_ends_with($shortName, 'Format')) {
                return strtolower(substr($shortName, 0, strlen($shortName) - 6));
            }
        }

        // If made it here...
        throw new RuntimeException(sprintf(
            'Could not automatically derive format name from class: %s',
            get_called_class()
        ));
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
        } catch (RuntimeException) {
            return '';
        }
    }

    public function getDocumentation(): ?string
    {
        // most formats don't add anything to the description
        return null;
    }


    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array<int,ParameterValidationRule>
     */
    abstract public function getValidationRules(): array;

    /**
     * Get built-in parameter preparation steps
     *
     * These run before validation
     *
     * @return array<int,PreparationStep>
     */
    public function getPreValidationPreparationSteps(): array
    {
        // Most formats do not have extra preparation steps.
        return [];
    }

    /**
     * Get built-in post-validation preparation steps
     *
     * These run after validation but before any custom preparation steps
     *
     * @return array<int,PreparationStep>
     */
    public function getPostValidationPreparationSteps(): array
    {
        return [];
    }
}
