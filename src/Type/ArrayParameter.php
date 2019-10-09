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

use Paramee\Contract\PreparationStep;
use Respect\Validation\Validator;
use Paramee\Model\Parameter;
use Paramee\Model\ParameterValidationRule;
use Paramee\ParamTypes;
use Paramee\PreparationStep\ArrayDeserializeStep;
use Paramee\PreparationStep\ArrayItemsPreparationStep;
use Paramee\Utility\FilterNull;
use stdClass;

/**
 * Class ArrayParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ArrayParameter extends Parameter
{
    public const TYPE_NAME = 'array';
    public const PHP_DATA_TYPE = 'array';

    /**
     * @var array|Parameter[]
     */
    private $allowedTypes = [];

    /**
     * @var bool
     */
    private $uniqueItems = false;

    /**
     * @var int|null
     */
    private $minItems = null;

    /**
     * @var int|null
     */
    private $maxItems = null;

    /**
     * @var array|PreparationStep[]
     */
    private $extraPreparationSteps = [];

    /**
     * @return array
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
     * @return ArrayParameter
     */
    final public function setUniqueItems(bool $uniqueItems): self
    {
        $this->uniqueItems = $uniqueItems;
        return $this;
    }

    /**
     * Set minimum items number of items (null for no minimum)
     *
     * @param int|null $minItems
     * @return ArrayParameter
     */
    final public function setMinItems(?int $minItems): ArrayParameter
    {
        $this->minItems = $minItems;
        return $this;
    }

    /**
     * Set maximum allowable number of items (null for no maximum)
     *
     * @param int|null $maxItems
     * @return ArrayParameter
     */
    final public function setMaxItems(?int $maxItems): ArrayParameter
    {
        $this->maxItems = $maxItems;
        return $this;
    }

    /**
     * @return array
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
     * @return ArrayParameter|self
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
     * @return ArrayParameter
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
     * @return array
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
     * @return array|ParameterValidationRule[]
     */
    protected function getBuiltInValidationRules(): array
    {
        if ($this->minItems !== null) {
            $rules[] = new ParameterValidationRule(
                Validator::length($this->minItems, null),
                sprintf('number of items in array must be greater than or equal to %s', number_format($this->minItems)),
                false
            );
        }

        if ($this->maxItems !== null) {
            $rules[] = new ParameterValidationRule(
                Validator::length(null, $this->maxItems),
                sprintf('number of items in array must be less than or equal to %s', number_format($this->maxItems)),
                false
            );
        }

        if ($this->uniqueItems) {
            $rules[] = new ParameterValidationRule(
                Validator::callback(function (array $value) {
                    return count(array_unique($value)) === count($value);
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
     * @return array|Parameter[]
     */
    private function listAllowedTypes(): array
    {
        $out = [];
        foreach ($this->allowedTypes as $type => $params) {
            $out = array_merge($out, $params);
        }
        return $out;
    }
}
