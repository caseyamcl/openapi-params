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

namespace Paramee\Type;

use Paramee\Contract\ParamFormatInterface;
use Paramee\Model\AbstractNumericParameter;
use Paramee\Format\DoubleFormat;
use Paramee\Format\FloatFormat;
use Paramee\PreparationStep\CallbackStep;

/**
 * Class NumberParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class NumberParameter extends AbstractNumericParameter
{
    public const TYPE_NAME = 'number';
    public const PHP_DATA_TYPE = null;

    /**
     * @var bool
     */
    private $requireDecimal = false;

    /**
     * NumberParameter constructor.
     *
     * @param string $name
     * @param bool $required
     * @param bool $requireDecimal  If TRUE, require that the number be either a 'float' or 'double' format
     */
    public function __construct(string $name, bool $required = false, bool $requireDecimal = false)
    {
        parent::__construct($name, $required);
        $this->setRequireDecimal($requireDecimal);
    }

    /**
     * @return ParamFormatInterface
     */
    protected function buildFormat(): ?ParamFormatInterface
    {
        return (PHP_FLOAT_DIG >= 15) ? new DoubleFormat() : new FloatFormat();
    }

    /**
     * @param bool $requireDecimal
     * @return NumberParameter
     */
    public function setRequireDecimal(bool $requireDecimal): self
    {
        $this->requireDecimal = $requireDecimal;
        $this->format = $this->requireDecimal ? $this->buildFormat() : null;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequireDecimal(): bool
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
