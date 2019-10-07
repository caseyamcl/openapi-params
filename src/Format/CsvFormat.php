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

namespace Paramee\Format;

use Paramee\Behavior\SetValidatorTrait;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;
use Paramee\Contract\ParameterValidationRuleInterface;
use Paramee\Contract\PreparationStepInterface;
use Paramee\Model\AbstractParamFormat;
use Paramee\Model\ParameterValidationRule;
use Paramee\PreparationStep\CallbackStep;
use Paramee\Type\StringParameter;
use Paramee\Utility\UnpackCSV;

/**
 * Comma-Separated-Value Format
 *
 * This is a custom format
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class CsvFormat extends AbstractParamFormat
{
    use SetValidatorTrait;

    public const TYPE_CLASS = StringParameter::class;
    public const NAME = 'csv';

    /**
     * @var array|string[]
     */
    private $separator = [','];

    /**
     * @var ParameterValidationRuleInterface
     */
    private $validatorForEach;

    /**
     * CsvFormat constructor.
     * @param string|array|string[] $separators
     */
    public function __construct($separators = ',')
    {
        $this->separator = is_array($separators) ? $separators : str_split($separators);
    }


    /**
     * Set the separator
     *
     * @param string $separator  Value will be split into an array (e.g. ';|' will become [';', '|'])
     * @return self
     */
    public function setSeparators(string $separator): self
    {
        $this->separator = str_split($separator);
        return $this;
    }

    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array|ParameterValidationRule[]
     */
    public function getValidationRules(): array
    {
        if ($this->validatorForEach) {
            return [
                new ParameterValidationRule(Validator::callback(function ($value) {
                    $items = UnpackCSV::un($value, $this->separator);
                    return Validator::each($this->validatorForEach->getValidator())->validate($items);
                }), $this->validatorForEach->getDocumentation())
            ];
        } else {
            return [];
        }
    }

    /**
     * Convenience method for setting validation (calls CsvFormat::setValidatorForEach())
     *
     * @param Validatable|ParameterValidationRule|callable $rule
     * @return self
     */
    public function each($rule): self
    {
        $this->setValidatorForEach($this->buildValidationRule($rule));
        return $this;
    }

    /**
     * Validate each item in the CSV list during validation
     *
     * @param ParameterValidationRuleInterface $rule
     */
    public function setValidatorForEach(ParameterValidationRuleInterface $rule)
    {
        $this->validatorForEach = $rule;
    }

    /**
     * Get built-in parameter preparation steps
     *
     * These run after validation but before any custom preparation steps
     *
     * @return array|PreparationStepInterface[]
     */
    public function getPreparationSteps(): array
    {
        return [
            new CallbackStep(function (string $rawValue): array {
                return UnpackCSV::un((string) $rawValue, $this->separator);
            }, 'unpacks value')
        ];
    }

    /**
     * @return string|null
     */
    public function getDocumentation(): ?string
    {
        $separator = implode('', $this->separator);
        return count($this->separator) > 1
            ? "Value must be a list of items delimited by one of the following: '$separator'."
            : "Value must be a list of items delimited by: '$separator'.";
    }
}
