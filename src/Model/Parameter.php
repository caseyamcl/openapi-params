<?php
/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\Model;

use LogicException;
use Paramee\Behavior\SetValidatorTrait;
use Respect\Validation\Validatable;
use Paramee\Contract\ParamFormatInterface;
use Paramee\Contract\PreparationStepInterface;
use Paramee\PreparationStep\AllowNullPreparationStep;
use Paramee\PreparationStep\DependencyCheckStep;
use Paramee\PreparationStep\EnsureCorrectDataTypeStep;
use Paramee\PreparationStep\EnumCheckStep;
use Paramee\PreparationStep\RespectValidationStep;
use Paramee\Utility\FilterNull;
use Paramee\Utility\RequireConstantTrait;

/**
 * Abstract Parameter
 *
 * Shared logic for all parameters
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class Parameter
{
    public const TYPE_NAME = null;
    public const PHP_DATA_TYPE = null;

    public const READ_ONLY  = 0;
    public const WRITE_ONLY = 1;
    public const READ_WRITE = 2;

    use RequireConstantTrait;
    use SetValidatorTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $required;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var bool
     */
    private $nullable = false;

    /**
     * @var array|null
     */
    private $enum = null;

    /**
     * @var array|mixed[]
     */
    private $examples = [];

    /**
     * @var bool
     */
    private $deprecated = false;

    /**
     * @var int
     */
    private $readWriteMode = self::READ_WRITE;

    /**
     * @var array|PreparationStepInterface[]
     */
    private $extraPreparationSteps = [];

    /**
     * @var array|ParameterValidationRule[]
     */
    private $validationRules = [];

    /**
     * @var ParamFormatInterface|null
     */
    protected $format = null;

    /**
     * @var bool
     */
    protected $allowTypeCast = false;

    /**
     * @var bool
     */
    private $defaultWasSet = false;

    /**
     * @var array|string[]  Other parameter names that should not be present if this parameter is present
     */
    private $dependsOnAbsenceOf = [];

    /**
     * @var array string[]|null[]  Other parameter names that must exist (and optional callback condition); keys are
     *                             parameter names, values are either callback or NULL
     */
    private $dependsOn = [];

    /**
     * AbstractParameter constructor (alternate syntax)
     *
     * @param string $name
     * @param bool $required
     * @return static
     */
    public static function create(string $name = '', bool $required = false)
    {
        return new static($name, $required);
    }

    /**
     * AbstractParameter constructor.
     *
     * @param string $name Parameter name
     * @param bool $required Is this a required parameter
     */
    public function __construct(string $name = '', bool $required = false)
    {
        $this->name = $name;
        $required ? $this->makeRequired() : $this->makeOptional();
    }

    /**
     * Get a copy of this parameter with a specific name
     *
     * @param string $name
     * @return static|Parameter
     */
    final public function withName(string $name): Parameter
    {
        $that = clone $this;
        $that->name = $name;
        $that->format = is_object($this->format) ? clone $this->format : $this->format;
        return $that;
    }

    /**
     * Add a validation rule
     *
     * @param callable|ParameterValidationRule|Validatable $rule
     * @param string $documentation Will be ignored if $rule is instance of ParameterValidationRule
     * @return Parameter
     */
    final public function addValidation($rule, string $documentation = ''): self
    {
        $this->validationRules[] = $this->buildValidationRule($rule, $documentation);
        return $this;
    }

    /**
     * @return string
     */
    final public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    final public function getTypeName(): string
    {
        return $this->requireConstant('TYPE_NAME');
    }

    /**
     * @return ParamFormatInterface|null
     */
    final public function getFormat(): ?ParamFormatInterface
    {
        return $this->format;
    }


    /**
     * @return string
     */
    final public function getDescription(): string
    {
        $description = $this->description;
        if ($this->getFormat()) {
            $description .= PHP_EOL . $this->format->getDocumentation();
        }
        // add preparation steps.

        foreach ($this->getPreparationSteps() as $step) {
            $description .= PHP_EOL . $step->getApiDocumentation();
        }

        return trim(preg_replace('/\s{2,}/', "\n", $description));
    }

    /**
     * @return bool
     */
    final public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    final public function hasDefault(): bool
    {
        return ($this->defaultWasSet && ! $this->isRequired());
    }

    /**
     * @return mixed
     */
    final public function getDefault()
    {
        return $this->default ?? null;
    }

    /**
     * Return NULL to not list acceptable values
     *
     * @return array|null
     */
    final public function getAllowedValues(): ?array
    {
        return $this->enum;
    }

    /**
     * @return bool
     */
    final public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return array
     */
    final public function listExamples(): array
    {
        return $this->examples;
    }

    /**
     * @return bool
     */
    final public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * @return bool
     */
    final public function isReadOnly(): bool
    {
        return $this->readWriteMode === self::READ_ONLY;
    }

    /**
     * @return bool
     */
    final public function isWriteOnly(): bool
    {
        return $this->readWriteMode === self::WRITE_ONLY;
    }

    /**
     * @param bool $allowTypeCast
     * @return static
     */
    final public function setAllowTypeCast(bool $allowTypeCast): self
    {
        $this->allowTypeCast = $allowTypeCast;
        return $this;
    }

    /**
     * @param string $description
     * @return self|Parameter
     */
    final public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param bool $nullable
     * @return self|Parameter
     */
    final public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * @param array|null $enum
     * @return self|Parameter
     */
    final public function setEnum(?array $enum): self
    {
        $this->enum = $enum;
        return $this;
    }

    /**
     * @param array|mixed[] $examples
     * @return self|Parameter
     */
    final public function setExamples($examples): self
    {
        $this->examples = $examples;
        return $this;
    }

    /**
     * @param bool $deprecated
     * @return self|Parameter
     */
    final public function setDeprecated(bool $deprecated): self
    {
        $this->deprecated = $deprecated;
        return $this;
    }

    /**
     * @param bool $readOnly
     * @return Parameter
     */
    final public function setReadOnly(bool $readOnly): self
    {
        if ($readOnly) {
            $this->readWriteMode = self::READ_ONLY;
        }

        return $this;
    }

    /**
     * @param bool $writeOnly
     * @return Parameter
     */
    final public function setWriteOnly(bool $writeOnly): self
    {
        if ($writeOnly) {
            $this->readWriteMode = self::WRITE_ONLY;
        }

        return $this;
    }

    /**
     * Set this to be a required value
     *
     * NOTE: If there is a default value and required is TRUE, this will throw an exception
     *
     * @param bool $isRequired
     * @return self|Parameter
     */
    final public function makeRequired(bool $isRequired = true): self
    {
        if ($this->hasDefault() && $isRequired === true) {
            throw new LogicException(sprintf(
                "Parameter (%s) cannot be both required and also contain a default value",
                $this->name
            ));
        }

        $this->required = $isRequired;
        return $this;
    }

    /**
     * Set this to be an optional value
     *
     * @param bool $isOptional
     * @return Parameter
     */
    final public function makeOptional(bool $isOptional = true): self
    {
        $this->makeRequired(! $isOptional);
        return $this;
    }

    /**
     * Set default value
     *
     * NULL is the only allowable value if this parameter is required
     *
     * @param mixed $default
     * @return self|Parameter
     */
    final public function setDefaultValue($default): self
    {
        if ($this->required === true) {
            throw new LogicException(sprintf(
                "Parameter (%s) cannot be both required and also contain a default value",
                $this->name
            ));
        }

        $this->default = $default;
        $this->defaultWasSet = true;
        return $this;
    }

    /**
     * Add a dependency for this parameter
     *
     * If this parameter is allowed only if another parameter is present, then add the other parameter name
     * using this method.  Additionally, you may optionally add an additional check (to check if the parameter has a
     * certain value or some-such) by passing a callback.
     *
     * @param string $otherParameterName
     * @param callable|null $callback     Optional callback; signature is $cb(ParameterValue $value): void
     *                                    The callback should throw an \InvalidArgument exception if value is invalid.
     * @return self|Parameter
     */
    final public function addDependsOn(string $otherParameterName, ?callable $callback = null): self
    {
        $this->dependsOn[$otherParameterName] = $callback;
        return $this;
    }

    /**
     * Indicate that this parameter is allowed only if another parameter is not present
     *
     * If this parameter is only allowed if another parameter is not present, then add that rule using this method.
     *
     * @param string $otherParameterName
     * @return Parameter
     */
    final public function addDependsOnAbsenceOf(string $otherParameterName): self
    {
        $this->dependsOnAbsenceOf[] = $otherParameterName;
        return $this;
    }

    /**
     * Add an extra preparation step
     *
     * @param PreparationStepInterface ...$step
     * @return self
     */
    final public function addPreparationStep(PreparationStepInterface ...$step): self
    {
        foreach ($step as $extraStep) {
            $this->extraPreparationSteps[] = $extraStep;
        }

        return $this;
    }

    // --------------------------------------------------------------
    // Parameter use-cases

    /**
     * Get OpenAPI-compatible documentation in the form of key/value pairs
     *
     * @return array
     */
    final public function getDocumentation(): array
    {
        $defaultDocumentation = FilterNull::filterNull([
            'type'        => (string) $this->getTypeName(),
            'required'    => $this->isRequired() ? true : null,
            'description' => $this->getDescription() ?: null,
            'format'      => $this->getFormat() ? (string) $this->getFormat() : null,
            'examples'    => (! empty($this->listExamples())) ? $this->listExamples() : null,
            'enum'        => (! empty($this->getAllowedValues())) ? $this->getAllowedValues() : null,
            'nullable'    => $this->isNullable() ? true : null,
            'deprecated'  => $this->isDeprecated() ? true : null,
            'readOnly'    => $this->isReadOnly() ? true : null
        ]);

        if ($this->hasDefault()) {
            $defaultDocumentation['default'] = $this->getDefault();
        }

        return array_merge($defaultDocumentation, $this->listExtraDocumentationItems());
    }

    /**
     * Get all validation rules
     *
     * @return array|ParameterValidationRule[]
     */
    final public function getValidationRules(): array
    {
        return array_merge(
            array_values($this->getBuiltInValidationRules()),
            array_values(($this->format ? $this->format->getValidationRules() : [])),
            array_values($this->validationRules)
        );
    }

    /**
     * List preparation steps in the order they are run
     *
     * @param bool $checkDependencies
     * @return ParameterPreparationSteps|PreparationStepInterface[]
     */
    final public function getPreparationSteps(bool $checkDependencies = true): ParameterPreparationSteps
    {
        // Dependency steps are always run, regardless of if NULL is allowed..
        $preSteps = [];

        if ($checkDependencies && ! empty($this->dependsOn)) {
            $preSteps[] = new DependencyCheckStep(
                array_keys($this->dependsOn),
                DependencyCheckStep::MUST_EXIST,
                array_filter($this->dependsOn)
            );
        }
        if ($checkDependencies && ! empty($this->dependsOnAbsenceOf)) {
            $preSteps[] = new DependencyCheckStep($this->dependsOnAbsenceOf, DependencyCheckStep::MUST_NOT_EXIST);
        }

        // Get pre-type-cast preparation steps
        $steps = $this->getPreTypeCastPreparationSteps();

        // Add type-check
        if ($phpTypes = $this->getPhpDataTypes()) {
            $steps[] = new EnsureCorrectDataTypeStep($phpTypes, $this->allowTypeCast);
        }

        // Add enum check
        if (is_array($this->enum)) {
            $steps[] = new EnumCheckStep($this->enum);
        }

        // Add built-in pre-validation preparation steps
        $steps = array_merge($steps, $this->getPreValidationPreparationSteps());

        // Add validation if there are validation rules
        $validationRules = $this->getValidationRules();
        if (!empty($validationRules)) {
            $steps[] = new RespectValidationStep($validationRules);
        }

        // Add the built-in post-validation preparation steps
        $steps = array_merge($steps, $this->getPostValidationPreparationSteps());

        // Add the built-in preparation steps from the format
        if ($this->format) {
            $steps = array_merge($steps, $this->format->getPreparationSteps());
        }

        // Merge the steps
        $steps = array_merge($steps, $this->extraPreparationSteps);

        // If allow null, then wrap each step with a decorator
        if ($this->nullable) {
            $steps = array_map(function (PreparationStepInterface $step) {
                return new AllowNullPreparationStep($step);
            }, $steps);
        }

        // Add user-defined extra steps and return
        return new ParameterPreparationSteps(array_merge($preSteps, $steps));
    }

    /**
     * Prepare the parameter
     *
     * @param mixed $value
     * @param ParameterValues $allValues
     * @return mixed|void
     */
    final public function prepare($value, ParameterValues &$allValues = null)
    {
        $myName = $this->__toString() ?: '(no name)';

        $checkDependencies = (bool) $allValues;
        $allValues = $allValues ?: new ParameterValues([$myName => $value]);
        $steps = $this->getPreparationSteps($checkDependencies);

        foreach ($steps as $idx => $step) {
            // Log message
            $logMessage = sprintf(
                "Parameter %s - Running preparation step %s/%s: %s",
                $myName,
                $idx + 1,
                count($steps),
                trim($step->__toString())
            );
            $allValues->getContext()->getLogger()->debug($logMessage);

            // Run it
            $value = $step($value, $myName, $allValues);

            // Update 'allValues'
            $allValues = $allValues->withPreparedValue($myName, $value);
        }

        return $value;
    }

    // --------------------------------------------------------------
    // Methods that can be overridden in child classes

    /**
     * Get the PHP data-type for this parameter
     *
     * @return array|string[]
     */
    public function getPhpDataTypes(): array
    {
        return [static::PHP_DATA_TYPE];
    }

    /**
     * Return whether or not this parameter allows typecast
     *
     * @return bool
     */
    public function allowsTypeCast(): bool
    {
        return $this->allowTypeCast;
    }

    /**
     * List parameter names that this parameter depends on (either being present or being not present)
     *
     * @return array|string[]  List of parameter names
     */
    public function listDependencies(): array
    {
        return array_unique(array_merge(array_keys($this->dependsOn), $this->dependsOnAbsenceOf));
    }

    // --------------------------------------------------------------

    /**
     * @return array
     */
    protected function listExtraDocumentationItems(): array
    {
        return []; // no extra values by default
    }

    /**
     * Get built-in parameter preparation steps that run before typecast/type check
     *
     * Used mainly for deserialization
     *
     * @return array
     */
    protected function getPreTypeCastPreparationSteps(): array
    {
        return [];
    }

    /**
     * Get built-in parameter preparation steps that run before validation step
     *
     * These run after type-check/type-cast but before validation
     *
     * @return array|PreparationStepInterface[]
     */
    protected function getPreValidationPreparationSteps(): array
    {
        return []; // most types don't have extra pre-validation steps.
    }

    /**
     * Get built-in parameter preparation steps that run after validation step
     *
     * These run after validation but before format-specific preparation steps
     *
     * @return array
     */
    protected function getPostValidationPreparationSteps(): array
    {
        return [];
    }

    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array|ParameterValidationRule[]
     */
    protected function getBuiltInValidationRules(): array
    {
        return [];
    }
}
