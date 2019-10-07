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

namespace Paramee\PreparationStep;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use Paramee\Contract\PreparationStepInterface;
use Paramee\Exception\InvalidValueException;
use Paramee\Model\ParameterValidationRule;
use Paramee\Model\ParameterValues;

/**
 * Respect Validation Step
 *
 * This step is built into the AbstractParameter, so if your parameter extends
 * that class, it will be run automatically.
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class RespectValidationStep implements PreparationStepInterface
{
    /**
     * @var array|ParameterValidationRule[]
     */
    private $rules = [];

    /**
     * @var Validator
     */
    private $validator;

    /**
     * RespectValidationStep constructor.
     * @param iterable $rules|ParameterValidationRule[]
     */
    public function __construct(iterable $rules)
    {
        $this->validator = new Validator();

        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    private function addRule(ParameterValidationRule $rule)
    {
        $this->rules[] = $rule;
        $this->validator->addRule($rule->getValidator());
    }

    /**
     * Get API Documentation for this step
     *
     * If this step defines a rule that is important to be included in the API documentation, then include
     * it here.  e.g. "value must be ..."
     *
     * @return string|null
     */
    public function getApiDocumentation(): ?string
    {
        return implode(PHP_EOL, array_map(function (ParameterValidationRule $rule) {
            return PHP_EOL . $rule->getDocumentation();
        }, $this->rules));
    }

    /**
     * Describe what this step does
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'Runs the following validation checks: ' . PHP_EOL . $this->getApiDocumentation();
    }

    /**
     * Prepare a parameter
     *
     * @param mixed $value
     * @param string $paramName
     * @param ParameterValues $allAllValues
     * @return mixed
     */
    public function __invoke($value, string $paramName, ParameterValues $allAllValues)
    {
        try {
            $this->validator->assert($value);
            return $value;
        } catch (NestedValidationException $e) {
            throw InvalidValueException::fromMessages($this, $paramName, $value, $e->getMessages());
        }
    }
}
