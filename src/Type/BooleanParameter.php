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

namespace OpenApiParams\Type;

use Respect\Validation\Validator;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Model\ParameterValidationRule;

/**
 * Class BooleanParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class BooleanParameter extends Parameter
{
    public const TYPE_NAME = 'boolean';
    public const PHP_DATA_TYPE = 'boolean';

    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array<int,ParameterValidationRule>
     */
    protected function getBuiltInValidationRules(): array
    {
        return [new ParameterValidationRule(Validator::boolType(), 'value must be boolean')];
    }
}
