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
use OpenApiParams\Type\StringParameter;

/**
 * OpenAPI String Binary Format
 *
 * @deprecated See https://spec.openapis.org/registry/format/
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class BinaryFormat extends AbstractParamFormat
{
    public const string TYPE_CLASS = StringParameter::class;
    public const string NAME = 'binary';

    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array<int,ParameterValidationRule>
     */
    public function getValidationRules(): array
    {
        return []; // no extra rules
    }
}
