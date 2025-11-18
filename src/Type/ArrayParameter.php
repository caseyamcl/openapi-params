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
use OpenApiParams\ParamTypes;
use OpenApiParams\PreparationStep\ArrayDeserializeStep;
use OpenApiParams\PreparationStep\ArrayItemsPreparationStep;
use OpenApiParams\Utility\FilterNull;
use stdClass;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class ArrayParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ArrayParameter extends Parameter
{
    public const string TYPE_NAME = 'array';
    public const string PHP_DATA_TYPE = 'array';

    /**
     * @var array<string,Parameter[]>
     */
    private array $allowedTypes = [];
    private bool $uniqueItems = false;
    private ?int $minItems = null;
    private ?int $maxItems = null;

    /**
     * @var array<int,PreparationStep>
     */
    private array $extraPreparationSteps = [];

    /**
     * @return array<string,mixed>
     */
    protected function listExtraDocumentationItems(): array
    {
        if (empty($this->allowedTypes)) {
            $items = new stdClass();
        } elseif (count($this->listAllowedTypes()) === 1) {
            $items = $this->listAllowedTypes()[0]->getDocumentation();
        } else {
            $items = ['oneOf' => array_map(function (Parameter $param) {
                return $param->getDocumentation();
            }, $this->listAllowedTypes())];
        }

        return FilterNull::filterNull([
            'items'       => $items,
            'uniqueItems' => $this->uniqueItems ?: null,
            'minItems'    => $this->minItems,
            'maxItems'    => $this->maxItems
        ]);
    }

    /**
     * Specify that all items must be unique
     *
     * @param bool $uniqueItems
     * @return self
     */
    final public function setUniqueItems(bool $uniqueItems): self
    {
        $this->uniqueItems = $uniqueItems;
        return $this;
    }

    /**
     * Set the minimum allowable number of items (null for no minimum)
     *
     * @param int|null $minItems
     * @return self
     */
    final public function setMinItems(?int $minItems): self
    {
        $this->minItems = $minItems;
        return $this;
    }

    /**
     * Set the maximum allowable number of items (null for no maximum)
     *
     * @param int|null $maxItems
     * @return self
     */
    final public function setMaxItems(?int $maxItems): self
    {
        $this->maxItems = $maxItems;
        return $this;
    }

    /**
     * @return array<int,PreparationStep>
     */
    protected function getPostValidationPreparationSteps(): array
    {
        return [
            new ArrayItemsPreparationStep($this->allowedTypes, $this->extraPreparationSteps)
        ];
    }

    /**
     * Alias for 'ArrayParameter::addPreparationStepForEach'
     *
     * @param PreparationStep $step
     * @return $this
     */
    final public function each(PreparationStep $step): self
    {
        return $this->addPreparationStepForEach($step);
    }

    /**
     * Add a preparation step for each item
     *
     * @param PreparationStep $step
     * @return $this
     */
    final public function addPreparationStepForEach(PreparationStep $step): self
    {
        $this->extraPreparationSteps[] = $step;
        return $this;
    }

    /**
     * Add an allowed type
     *
     * Must be 'string', 'integer', 'array', 'object', 'number', or 'boolean'
     *
     * @param string ...$type
     * @return self
     */
    final public function addAllowedType(string ...$type): self
    {
        foreach ($type as $t) {
            $this->addAllowedParamDefinition(ParamTypes::resolveTypeInstance($t));
        }

        return $this;
    }

    /**
     * Add an allowed type in the form of a parameter
     *
     * @param Parameter $parameter
     * @return self
     */
    final public function addAllowedParamDefinition(Parameter $parameter): self
    {
        foreach ($parameter->getPhpDataTypes() as $dataType) {
            $this->allowedTypes[$dataType][] = $parameter;
        }

        return $this;
    }

    /**
     * Array Deserialize is a pre-type cast preparation step
     *
     * @return array<int,PreparationStep>
     */
    protected function getPreTypeCastPreparationSteps(): array
    {
        return [
            new ArrayDeserializeStep()
        ];
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
        if ($this->minItems !== null) {
            $rules[] = new ParameterValidationRule(
                new Count(min: $this->minItems),
                sprintf('number of items in array must be greater than or equal to %s', number_format($this->minItems)),
                false
            );
        }

        if ($this->maxItems !== null) {
            $rules[] = new ParameterValidationRule(
                new Count(max: $this->maxItems),
                sprintf('number of items in array must be less than or equal to %s', number_format($this->maxItems)),
                false
            );
        }

        if ($this->uniqueItems) {
            $rules[] = new ParameterValidationRule(
                new Callback(function (array $value, ExecutionContextInterface $ctx) {
                    if (count(array_unique($value)) !== count($value)) {
                        $ctx->addViolation('values must be unique items');
                    }
                }),
                'values must be unique items',
                false
            );
        }

        return $rules ?? [];
    }

    /**
     * List allowed types for data in the array
     *
     * Flatten the allowedTypes property
     *
     * @return array<int,Parameter>
     */
    private function listAllowedTypes(): array
    {
        $out = [];
        foreach ($this->allowedTypes as $params) {
            $out = array_merge($out, $params);
        }
        return $out;
    }
}
