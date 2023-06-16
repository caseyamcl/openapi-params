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

use OpenApiParams\Behavior\SetValidatorTrait;
use OpenApiParams\Contract\ParamValidationRule;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\PreparationStep\CallbackStep;
use OpenApiParams\Type\StringParameter;
use OpenApiParams\Utility\UnpackCSV;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface as Context;

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
     * @var array<int, string>
     */
    private array $separator = [','];

    private ?ParamValidationRule $validatorForEach = null;

    /**
     * CsvFormat constructor.
     * @param string|array<int,string> $separators
     */
    public function __construct(string|array $separators = ',')
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
     * @return array<int,ParameterValidationRule>
     */
    public function getValidationRules(): array
    {
        // TODO: LEFT OFF HERE. Figure out how to do this under new Symfony paradigm
        // OLD:
        //  new ParameterValidationRule(Validator::callback(function ($value) {
        //      $items = UnpackCSV::un($value, $this->separator);
        //      return Validator::each($this->validatorForEach->getValidator())->validate($items);
        //      }), $this->validatorForEach->getDescription())
        if ($this->validatorForEach) {
            return [
                new ParameterValidationRule(
                    new Callback(function (Context $context, $value) {
                        $items = UnpackCSV::un($value, $this->separator);
                        return (new All(['constraints' => [
                            $this->validatorForEach->getValidator()
                        ]]));
                    }),
                    $this->validatorForEach->getDescription()
                )
            ];
        } else {
            return [];
        }
    }

    /**
     * Convenience method for setting validation (calls CsvFormat::setValidatorForEach())
     */
    public function each(Validatable|ParamValidationRule|callable $rule): self
    {
        $this->setValidatorForEach($this->buildValidationRule($rule));
        return $this;
    }

    /**
     * Validate each item in the CSV list during validation
     *
     * @param ParamValidationRule $rule
     */
    public function setValidatorForEach(ParamValidationRule $rule): void
    {
        $this->validatorForEach = $rule;
    }

    /**
     * Get built-in parameter preparation steps
     *
     * These run after validation but before any custom preparation steps
     *
     * @return array<int,PreparationStep>
     */
    public function getPreparationSteps(): array
    {
        return [
            new CallbackStep(function (string $rawValue): array {
                return UnpackCSV::un((string) $rawValue, $this->separator);
            }, 'unpacks value')
        ];
    }

    public function getDocumentation(): ?string
    {
        $separator = implode('', $this->separator);
        return count($this->separator) > 1
            ? "Value must be a list of items delimited by one of the following: '$separator'."
            : "Value must be a list of items delimited by: '$separator'.";
    }
}
