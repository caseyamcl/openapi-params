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

use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Model\AbstractNumericParameter;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Format\Int32Format;
use OpenApiParams\Format\Int64Format;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Class IntegerParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class IntegerParameter extends AbstractNumericParameter
{
    public const TYPE_NAME = 'integer';
    public const PHP_DATA_TYPE = 'integer';

    /**
     * @return ParamFormat
     */
    protected function buildFormat(): ParamFormat
    {
        return PHP_INT_SIZE === 8 ? new Int64Format() : new Int32Format();
    }

    protected function getBuiltInValidationRules(): array
    {
        return array_merge(
            parent::getBuiltInValidationRules(),
            [new ParameterValidationRule(
                new Type('integer'),
                'value must be an integer',
                false
            )]
        );
    }

    protected function init(): void
    {
        $this->format = $this->buildFormat(); // integer types will always have a format
    }
}
