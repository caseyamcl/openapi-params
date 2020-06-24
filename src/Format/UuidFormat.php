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

namespace OpenApiParams\Format;

use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Type\StringParameter;
use Respect\Validation\Rules\Uuid;

/**
 * Class UuidFormat
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class UuidFormat extends AbstractParamFormat
{
    public const NAME = 'uuid';
    public const TYPE_CLASS = StringParameter::class;

    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array|ParameterValidationRule[]
     */
    public function getValidationRules(): array
    {
        return [
            new ParameterValidationRule(
                new Uuid(),
                'value must be a valid UUID'
            )
        ];
    }

    /**
     * Get built-in parameter preparation steps
     *
     * These run after validation but before any custom preparation steps
     *
     * @return array|PreparationStep[]
     */
    public function getPreparationSteps(): array
    {
        return []; // no extra preparation steps.
    }
}
