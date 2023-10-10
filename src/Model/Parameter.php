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

namespace OpenApiParams\Model;

use LogicException;
use OpenApiParams\Behavior\SetValidatorTrait;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\PreparationStep\AllowNullPreparationStep;
use OpenApiParams\PreparationStep\DependencyCheckStep;
use OpenApiParams\PreparationStep\EnsureCorrectDataTypeStep;
use OpenApiParams\PreparationStep\EnumCheckStep;
use OpenApiParams\PreparationStep\ValidationStep;
use OpenApiParams\Utility\FilterNull;
use OpenApiParams\Utility\RequireConstantTrait;
use Symfony\Component\Validator\Constraint;

/**
 * Abstract Parameter
 *
 * Shared logic for all parameters
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class Parameter
{
    use RequireConstantTrait;
    use SetValidatorTrait;

    public const TYPE_NAME = null;
    public const PHP_DATA_TYPE = null;

    public const READ_ONLY  = 0;
    public const WRITE_ONLY = 1;
    public const READ_WRITE = 2;

    private string $name;
    private string $description = '';
    private bool $required = false;
    private mixed $default = null;
    private bool $nullable = false;
    private ?array $enum = null;
    private ?array $examples = [];
    private bool $deprecated = false;
    private int $readWriteMode = self::READ_WRITE;
    protected ?ParamFormat $format = null;
    protected bool $allowTypeCast = false;
    private bool $defaultWasSet = false;

    /**
     * @var array<int,PreparationStep>
     */
    private array $extraPreparationSteps = [];
    /**
     * @var array<int,ParameterValidationRule>
     */
    private array $validationRules = [];
    /**
     * @var array<int,string>  Other parameter names that should not be present if this parameter is present
     */
    private array $dependsOnAbsenceOf = [];
    /**
     * @var array<string,callable|null>  Other parameter names that must exist (and optional callback condition);
     *                                   keys are parameter names, values are either callback or NULL
     */
    private array $dependsOn = [];

    /**
     * @var array<string,callable|null>  Other parameter names that can optionally exist. Same format as self::dependsOn
     */
    private array $processAfter = [];

    /**
     * AbstractParameter constructor (alternate syntax)
     *
     * @param string $name
     * @param bool $required
     * @return static
     */
    public static function create(string $name = '', bool $required = false): static
    {
        return new static($name, $required);
    }

    /**
     * AbstractParameter constructor.
     *
     * @param string $name Parameter name
     * @param bool $required Is this a required parameter
     */
    final public function __construct(string $name = '', bool $required = false)
    {
        $this->name = $name;
        $required ? $this->makeRequired() : $this->makeOptional();
        $this->init();
    }

    /**
     * Get a copy of this parameter with a specific name
     *
     * @param string $name
     * @return static
     */
    final public function withName(string $name): static
    {
        $that = clone $this;
        $that->name = $name;
        $that->format = is_object($this->format) ? clone $this->format : $this->format;
        return $that;
    }

    /**
     * Add a validation rule
     *
     * @param callable|ParameterValidationRule|Constraint $rule
     * @param string $documentation Will be ignored if $rule is instance of ParameterValidationRule
     * @return static
     */
    final public function addValidationRule(
        callable|ParameterValidationRule|Constraint $rule,
        string $documentation = ''
    ): static {
        $this->validationRules[] = $this->buildValidationRule($rule, $documentation);
        return $this;
    }

    /**
     * Add multiple validation rules
     *
     * NOTE: If passing callbacks or instances of Validatable (Respect rules), the library
     * assumes that there is no documentation associated with the rules.
     *
     * To pass documentation, pass instances of ParameterValidationRule or use self::addValidationRule()
     *
     * @param callable|ParameterValidationRule|Constraint ...$rules
     * @return static
     */
    final public function addValidationRules(callable|ParameterValidationRule|Constraint ...$rules): static
    {
        foreach ($rules as $rule) {
            $this->addValidationRule($rule);
        }

        return $this;
    }

    final public function __toString(): string
    {
        return $this->name;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function getTypeName(): string
    {
        return $this->requireConstant('TYPE_NAME');
    }

    final public function getFormat(): ?ParamFormat
    {
        return $this->format;
    }

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

    final public function isRequired(): bool
    {
        return $this->required;
    }

    final public function hasDefault(): bool
    {
        return ($this->defaultWasSet && ! $this->isRequired());
    }

    /**
     * Returns NULL if no default defined
     */
    final public function getDefault(): mixed
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

    final public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return array<int,mixed>
     */
    final public function listExamples(): array
    {
        return $this->examples;
    }

    final public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    final public function isReadOnly(): bool
    {
        return $this->readWriteMode === self::READ_ONLY;
    }

    final public function isWriteOnly(): bool
    {
        return $this->readWriteMode === self::WRITE_ONLY;
    }

    final public function setAllowTypeCast(bool $allowTypeCast): static
    {
        $this->allowTypeCast = $allowTypeCast;
        return $this;
    }

    final public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    final public function setNullable(bool $nullable): static
    {
        $this->nullable = $nullable;
        return $this;
    }

    final public function setEnum(?array $enum): static
    {
        $this->enum = $enum;
        return $this;
    }

    /**
     * @param array<int,mixed> $examples
     */
    final public function setExamples(array $examples): static
    {
        $this->examples = $examples;
        return $this;
    }

    final public function setDeprecated(bool $deprecated): static
    {
        $this->deprecated = $deprecated;
        return $this;
    }

    final public function setReadOnly(bool $readOnly): static
    {
        if ($readOnly) {
            $this->readWriteMode = self::READ_ONLY;
        }

        return $this;
    }

    final public function setWriteOnly(bool $writeOnly): static
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
     */
    final public function makeRequired(bool $isRequired = true): static
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
     */
    final public function makeOptional(bool $isOptional = true): static
    {
        $this->makeRequired(! $isOptional);
        return $this;
    }

    /**
     * Set default value
     *
     * NULL is the only allowable value if this parameter is required
     */
    final public function setDefaultValue(mixed $default): static
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
     * using this method. Additionally, you may optionally add a check (to check if the parameter has a
     * certain value or some-such) by passing a callback.
     *
     * @param string $otherParameterName
     * @param callable|null $callback     Optional callback; signature is $cb(ParameterValue $value): void
     *                                    The callback should throw an \InvalidArgument exception if value is invalid.
     * @return static
     */
    final public function addDependsOn(string $otherParameterName, ?callable $callback = null): static
    {
        $this->dependsOn[$otherParameterName] = $callback;
        return $this;
    }

    /**
     * Indicate that this parameter is allowed only if another parameter is not present
     *
     * If this parameter is only allowed if another parameter is not present, then add that rule using this method.
     */
    final public function addDependsOnAbsenceOf(string $otherParameterName): static
    {
        $this->dependsOnAbsenceOf[] = $otherParameterName;
        return $this;
    }

    /**
     * List of parameter names that, if they are present, must be processed first
     *
     * The difference between this and `dependsOn()` is that the other parameters do not have to exist.
     *
     * Additionally, you may optionally add a check (to check if the parameter has a certain value or some-such)
     * by passing a callback.
     */
    final public function addProcessAfter(string $otherParameterName, ?callable $callback = null): static
    {
        $this->processAfter[$otherParameterName] = $callback;
        return $this;
    }

    /**
     * Add a preparation step
     */
    final public function addPreparationStep(PreparationStep ...$step): static
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
     * @return array<string,mixed>
     */
    final public function getDocumentation(): array
    {
        $defaultDocumentation = FilterNull::filterNull([
            'type'        => $this->getTypeName(),
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
     * @return array<int,ParameterValidationRule>
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
     * @return ParameterPreparationSteps<int,PreparationStep>
     */
    final public function getPreparationSteps(bool $checkDependencies = true): ParameterPreparationSteps
    {
        // Dependency steps are always run, regardless of if NULL is allowed
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

        if ($checkDependencies && ! empty($this->processAfter)) {
            $preSteps[] = new DependencyCheckStep(
                array_keys($this->processAfter),
                DependencyCheckStep::MAY_EXIST,
                array_filter($this->processAfter)
            );
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

        // Add the built-in pre-validation preparation steps from the format
        if ($this->format) {
            $steps = array_merge($steps, $this->format->getPreValidationPreparationSteps());
        }

        // Add validation if there are validation rules
        $validationRules = $this->getValidationRules();
        if (!empty($validationRules)) {
            $steps[] = new ValidationStep($validationRules);
        }

        // Add the built-in post-validation preparation steps
        $steps = array_merge($steps, $this->getPostValidationPreparationSteps());

        // Add the built-in post-validation preparation steps from the format
        if ($this->format) {
            $steps = array_merge($steps, $this->format->getPostValidationPreparationSteps());
        }

        // Add user-defined preparation steps
        $steps = array_merge($steps, $this->extraPreparationSteps);

        // If allow null, then wrap each step with a decorator
        if ($this->nullable) {
            $steps = array_map(function (PreparationStep $step) {
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
     * @param ParameterValues|null $allValues
     * @return mixed
     */
    final public function prepare(mixed $value, ?ParameterValues &$allValues = null): mixed
    {
        $myName = $this->getName() ?: '(no name)';

        $checkDependencies = (bool) $allValues;
        $allValues = $allValues ?: new ParameterValues([$myName => $value]);
        $steps = $this->getPreparationSteps($checkDependencies);

        foreach ($steps as $idx => $step) {
            // Log message
            $logMessage = sprintf(
                "Parameter: %s - Running preparation step %s/%s: %s",
                $myName,
                $idx + 1,
                count($steps),
                trim($step->__toString())
            );
            $allValues->getContext()->getLogger()->debug($logMessage, ['name' => $this->getName()]);

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
     * @return array<int,string>
     */
    public function getPhpDataTypes(): array
    {
        return [static::PHP_DATA_TYPE];
    }

    /**
     * Return whether this parameter allows to typecast
     */
    public function allowsTypeCast(): bool
    {
        return $this->allowTypeCast;
    }

    /**
     * List parameter names that this parameter depends on (either being present or being not present)
     *
     * @return array<int,string>  List of parameter names
     */
    public function listDependencies(): array
    {
        return array_unique(array_merge(array_keys($this->dependsOn), $this->dependsOnAbsenceOf));
    }

    /**
     * List parameter names that this parameter must run after
     *
     * @param array $allParamNames<int,string>
     * @return array<int,string>
     */
    public function listOptionalDependencies(array $allParamNames): array
    {
        return array_intersect(array_keys($this->processAfter), $allParamNames);
    }

    // --------------------------------------------------------------

    /**
     * @return array<string,mixed>
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
     * @return array<int,PreparationStep>
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
     * @return array<int,PreparationStep>
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
     * @return array<int,PreparationStep>
     */
    protected function getPostValidationPreparationSteps(): array
    {
        return [];
    }

    protected function init(): void
    {
        // default no-op
    }

    /**
     * Get built-in validation rules
     *
     * These automatically are added to the validation preparation step
     *
     * @return array<int,ParameterValidationRule>
     */
    abstract protected function getBuiltInValidationRules(): array;
}
