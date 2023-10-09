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

namespace OpenApiParams\Contract;

use OpenApiParams\Model\ParameterValidationRule;

/**
 * Parameter Format
 *
 * OpenApi Parameter Type
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
interface ParamFormat
{
    /**
     * Return the type class that this format can be applied to
     */
    public function appliesToType(): string;

    /**
     * Get the name of the format
     */
    public function __toString(): string;

    /**
     * If this format adds any documentation to the parameter description, return that here.
     *
     * Note: This can include line breaks
     */
    public function getDocumentation(): ?string;

    /**
     * Get validation rules for this type (always validates against raw value)
     *
     * @return array<int,ParameterValidationRule>
     */
    public function getValidationRules(): array;

    /**
     * Get built-in preparation steps that run before validation
     *
     * @return array<int,PreparationStep>
     */
    public function getPreValidationPreparationSteps(): array;

    /**
     * Get built-in preparation steps that run after validation
     *
     * @return array
     */
    public function getPostValidationPreparationSteps(): array;
}
