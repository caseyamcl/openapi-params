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

use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Type\NumberParameter;

/**
 * OpenAPI Float Format
 *
 * This is automatically assigned based on the system's numeric precision capabilities
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class FloatFormat extends AbstractParamFormat
{
    public const TYPE_CLASS = NumberParameter::class;

    public function getValidationRules(): array
    {
        return []; // no extra validation rules
    }
}
