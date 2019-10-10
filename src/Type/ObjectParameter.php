<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/openapi-params
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\Type;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\PreparationStep\ObjectDeserializeStep;
use OpenApiParams\PreparationStep\PrepareObjectPropertiesStep;
use OpenApiParams\Utility\FilterNull;
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
     * @var array|Parameter[]
     */
    private $properties = [];

    /**
     * @var bool|null  If AUTO (null), then if properties defined, FALSE, else TRUE
     */
    private $allowAdditionalProperties = self::AUTO;

    /**
     * @var int|null
     */
    private $minProperties = null;

    /**
     * @var int|null
     */
    private $maxProperties = null;

    /**
     * @var string
     */
    private $schemaName = null;

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
     * @return array|Parameter[]
     */
    final public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set whether or not this parameter should allow additional, arbitrary properties (default is false)
     *
     * @param bool $allow
     * @return self
     */
    final public function setAllowAdditionalProperties(bool $allow): self
    {
        $this->allowAdditionalProperties = $allow;
        return $this;
    }

    /**
     * Set minimum allowable number of properties
     *
     * @param int|null $min
     * @return ObjectParameter
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
     * @return $this
     */
    final public function setMaxProperties(?int $max)
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
     * @return ObjectParameter
     */
    final public function addProperty(Parameter $parameter): self
    {
        Assert::notEmpty($parameter->getName(), 'cannot add parameter without a name to an object');

        $this->properties[$parameter->getName()] = $parameter;
        return $this;
    }

    /**
     * @param Parameter ...$parameters
     * @return ObjectParameter
     */
    final public function addProperties(Parameter ...$parameters): self
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
     * @return array|ParameterValidationRule[]
     */
    protected function getBuiltInValidationRules(): array
    {
        // Minimum number of properties?
        if ($this->minProperties) {
            $rules[] = new ParameterValidationRule(
                Validator::callback(function ($obj) {
                    return count((array) $obj) >= $this->minProperties;
                }),
                sprintf(
                    'number of properties in object must be greater than or equal to %s',
                    number_format($this->minProperties)
                ),
                false
            );
        }

        // Maximum number of properties?
        if ($this->maxProperties) {
            $rules[] = new ParameterValidationRule(
                Validator::callback(function ($obj) {
                    return count((array) $obj) <= $this->maxProperties;
                }),
                sprintf(
                    'number of properties in object must be less than or equal to %s',
                    number_format($this->maxProperties)
                ),
                false
            );
        }

        // Required parameters
        $rules[] = new ParameterValidationRule(
            Validator::callback(function ($obj) {
                $required = array_filter(array_keys($this->properties), function (string $name) {
                    return $this->properties[$name]->isRequired();
                });

                $diff = array_diff($required, array_keys((array) $obj));
                if (empty($diff)) {
                    return true;
                } else {
                    throw new ValidationException(sprintf(
                        'missing required properties: %s',
                        implode(', ', $diff)
                    ));
                }
            }),
            sprintf('object must contain properties: %s', implode(', ', array_keys($this->properties))),
            false
        );

        // Extra undefined parameters
        $allowAdditional = (is_bool($this->allowAdditionalProperties))
            ? $this->allowAdditionalProperties
            : empty($this->properties);

        if (! $allowAdditional) {
            $rule = Validator::callback(function ($obj) {
                $diff = array_diff(array_keys((array) $obj), array_keys($this->properties));
                return count($diff) == 0;
            });

            $invalidMsg = sprintf(
                'value can only contain properties: %s',
                implode(', ', array_keys($this->properties)
            ));

            $rules[] = new ParameterValidationRule(Validator::callback($rule)->setTemplate($invalidMsg), $invalidMsg);
        }

        return $rules ?? [];
    }

    /**
     * List any extra OpenApi documentation values
     *
     * Certain data types in OpenApi list additional properties in the schema.  Override this method
     * to add those when self-creating documentation
     *
     * @return array  Key/value pairs of additional OpenApi Documentation properties
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
                if ($extra['properties'][$propName]['required'] == true) {
                    $extra['required'][] = $propName;
                }
                unset($extra['properties'][$propName]['required']);
            }
        }

        return $extra;
    }

    /**
     * @return array
     */
    protected function getPreTypeCastPreparationSteps(): array
    {
        return [new ObjectDeserializeStep()];
    }


    /**
     * @return array|PreparationStep[]
     */
    protected function getPostValidationPreparationSteps(): array
    {
        return [new PrepareObjectPropertiesStep($this)];
    }
}
