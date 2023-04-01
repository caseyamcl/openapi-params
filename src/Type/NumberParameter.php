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
use OpenApiParams\Format\DoubleFormat;
use OpenApiParams\Format\FloatFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\PreparationStep\CallbackStep;
use Respect\Validation\Validator;

/**
 * Class NumberParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class NumberParameter extends AbstractNumericParameter
{
    public const TYPE_NAME = 'number';
    public const PHP_DATA_TYPE = null;

    private bool $requireDecimal = false;

    protected function buildFormat(): ParamFormat
    {
        return (PHP_FLOAT_DIG >= 15) ? new DoubleFormat() : new FloatFormat();
    }

    /**
     * @param bool $requireDecimal
     * @return NumberParameter
     */
    final public function setRequireDecimal(bool $requireDecimal): self
    {
        $this->requireDecimal = $requireDecimal;
        $this->format = $this->requireDecimal ? $this->buildFormat() : null;
        return $this;
    }

    /**
     * @return bool
     */
    final public function isRequireDecimal(): bool
    {
        return $this->requireDecimal;
    }

    protected function getPostValidationPreparationSteps(): array
    {
        return [
            new CallbackStep(function ($value) {
                return (PHP_FLOAT_DIG >= 15) ? (double) $value : (float) $value;
            }, 'type-cast to float or double if integer')
        ];
    }

    protected function getBuiltInValidationRules(): array
    {
        return array_merge(parent::getBuiltInValidationRules(), [
            new ParameterValidationRule(
                Validator::oneOf(Validator::intType(), Validator::floatType()),
                'value must be an integer or a float',
                false
            )
        ]);
    }

    /**
     * Get the PHP data-type for this parameter
     *
     * @return array|string[]
     */
    public function getPhpDataTypes(): array
    {
        $decimalFormat = (PHP_FLOAT_DIG >= 15) ? 'double' : 'float';

        // Only require that the value be a float/double if explicitly declared that it must be a decimal
        if ($this->requireDecimal) {
            return [$decimalFormat];
        } else {
            return [$decimalFormat, IntegerParameter::PHP_DATA_TYPE];
        }
    }
}
