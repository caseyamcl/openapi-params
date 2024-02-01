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

use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\PreparationStep\ObjectDeserializeStep;
use OpenApiParams\PreparationStep\PrepareObjectPropertiesStep;
use OpenApiParams\Utility\FilterNull;
use OpenApiParams\Validator\ValidObjectExtraProperties;
use OpenApiParams\Validator\ValidObjectProperties;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Webmozart\Assert\Assert;

/**
 * Class ObjectParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ObjectParameter extends Parameter
{
    protected const AUTO = null;

    public const TYPE_NAME = 'object';
    public const PHP_DATA_TYPE = 'object';

    /**
     * @var array<int,Parameter>
     */
    private array $properties = [];

    /**
     * If AUTO (null), then if properties defined, FALSE, else TRUE
     */
    private ?bool $allowAdditionalProperties = self::AUTO;

    private ?int $minProperties = null;

    private ?int $maxProperties = null;

    private ?string $schemaName = null;

    /**
     * Set a schema name in-case we want to re-use this parameter and reference it in multiple locations
     *
     * @param string $schemaName
     * @return ObjectParameter
     */
    public function setSchemaName(string $schemaName): self
    {
        $this->schemaName = $schemaName;
        return $this;
    }

    /**
     * Get the schema name (if set)
     * @return string|null
     */
    final public function getSchemaName(): ?string
    {
        return $this->schemaName;
    }

    /**
     * @return array<int,Parameter>
     */
    final public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Set whether this parameter should allow additional, arbitrary properties (default is auto-detect)
     *
     * Auto-detect means that if no properties are explicitly defined, assume that this object allows additional
     * properties
     *
     * @param bool $allow
     * @return self
     */
    final public function setAllowAdditionalProperties(?bool $allow): self
    {
        $this->allowAdditionalProperties = $allow;
        return $this;
    }

    /**
     * Set minimum allowable number of properties
     *
     * @param int|null $min
     * @return self
     */
    final public function setMinProperties(?int $min): self
    {
        if (! is_null($min)) {
            Assert::natural($min);
        }

        $this->minProperties = $min;
        return $this;
    }

    /**
     * Set the maximum allowable properties
     *
     * @param int|null $max
     * @return self
     */
    final public function setMaxProperties(?int $max): self
    {
        if (! is_null($max)) {
            Assert::natural($max);
        }

        $this->maxProperties = $max;
        return $this;
    }

    /**
     * Add a property
     *
     * @param Parameter $parameter
     * @return self
     */
    final public function addProperty(Parameter $parameter): self
    {
        Assert::notEmpty($parameter->getName(), 'cannot add parameter without a name to an object');

        $this->properties[$parameter->getName()] = $parameter;
        return $this;
    }

    /**
     * @param Parameter ...$parameters
     * @return self
     */
    final public function addProperties(Parameter ...$parameters): self
    {
        foreach ($parameters as $param) {
            $this->addProperty($param);
        }

        return $this;
    }

    /**
     * @param iterable<Parameter> $properties
     * @return self
     */
    final public function addPropertyList(iterable $parameters): self
    {
        foreach ($parameters as $param) {
            $this->addProperty($param);
        }

        return $this;
    }

    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array<int,ParameterValidationRule>
     */
    protected function getBuiltInValidationRules(): array
    {
        $rules = [];

        // Minimum number of properties?
        if ($this->minProperties !== null) {
            $minNum = number_format($this->minProperties);
            $rules[] = new ParameterValidationRule(
                new Callback(function ($obj, ExecutionContextInterface $ctx) use ($minNum) {
                    if (count((array) $obj) < $this->minProperties) {
                        $ctx->addViolation("number of properties in object must be greater than or equal to $minNum");
                    }
                }),
                "number of properties in object must be greater than or equal to $minNum",
                false
            );
        }

        // Maximum number of properties?
        if ($this->maxProperties !== null) {
            $maxNum = number_format($this->maxProperties);
            $rules[] = new ParameterValidationRule(
                new Callback(function ($obj, ExecutionContextInterface $ctx) use ($maxNum) {
                    if (count((array) $obj) > $this->maxProperties) {
                        $ctx->addViolation("number of properties in {{name}} must be less than or equal to $maxNum");
                    }
                }),
                "number of properties in object must be less than or equal to $maxNum",
                false
            );
        }

        // Required Properties?
        $requiredProperties = array_filter($this->properties, function (Parameter $param) {
            return $param->isRequired();
        });
        $rules[] = new ParameterValidationRule(
            new ValidObjectProperties($requiredProperties),
            sprintf('object must contain properties: %s', implode(', ', $requiredProperties)),
            false
        );

        // Extra additional, arbitrary properties?
        $allowAdditional = (is_bool($this->allowAdditionalProperties))
            ? $this->allowAdditionalProperties
            : empty($this->properties);
        if (! $allowAdditional) {
            $allowedExtraProps = array_keys($this->properties);
            $rules[] = new ParameterValidationRule(
                new ValidObjectExtraProperties($allowedExtraProps),
                sprintf('value can only contain properties: %s', implode(', ', $allowedExtraProps))
            );
        }

        return $rules;
    }

    /**
     * List any extra OpenApi documentation values
     *
     * Certain data types in OpenApi list additional properties in the schema.  Override this method
     * to add those when self-creating documentation
     *
     * @return array<string,mixed>  Key/value pairs of additional OpenApi Documentation properties
     */
    protected function listExtraDocumentationItems(): array
    {
        $extra = FilterNull::filterNull([
            'additionalProperties' => $this->allowAdditionalProperties ?: null,
            'minProperties'        => $this->minProperties,
            'maxProperties'        => $this->maxProperties,
            'properties'           => []
        ]);

        foreach ($this->properties as $propName => $parameter) {
            $extra['properties'][$propName] = $parameter->getDocumentation();

            // Per OpenAPI spec, object properties cannot have 'required' attribute,
            // so we remove it, but if it is true, there is a special 'required' attribute for the
            // parent object that must have the property name in it.  Sheesh...
            if (isset($extra['properties'][$propName]['required'])) {
                if ($extra['properties'][$propName]['required']) {
                    $extra['required'][] = $propName;
                }
                unset($extra['properties'][$propName]['required']);
            }
        }

        return $extra;
    }

    /**
     * @return array<int,PreparationStep>
     */
    protected function getPreTypeCastPreparationSteps(): array
    {
        return [new ObjectDeserializeStep()];
    }


    /**
     * @return array<int,PreparationStep>
     */
    protected function getPostValidationPreparationSteps(): array
    {
        return [new PrepareObjectPropertiesStep($this)];
    }
}
