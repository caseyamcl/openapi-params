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

namespace OpenApiParams\PreparationStep;

use OpenApiParams\Utility\ValidatorFactory;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Model\ParameterValues;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validation Step
 *
 * This step is built into the AbstractParameter, so if your parameter extends
 * that class, it will be run automatically.
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidationStep implements PreparationStep
{
    /**
     * @var array<int,ParameterValidationRule>
     */
    private array $rules = [];

    private ValidatorInterface $validator;

    /**
     * @param iterable $rules<int,\Symfony\Validator\Constraint>
     */
    public function __construct(iterable $rules)
    {
        $this->validator = ValidatorFactory::build();

        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    private function addRule(ParameterValidationRule $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * Returns a list of validation rule descriptions separated by line-breaks
     */
    public function getApiDocumentation(): ?string
    {
        return implode(PHP_EOL, array_filter(array_map(function (ParameterValidationRule $rule) {
            return $rule->includeInDocumentation() ? PHP_EOL . $rule->getDescription() : null;
        }, $this->rules)));
    }

    public function __toString(): string
    {
        return 'runs the following validation checks: ' . PHP_EOL . trim($this->getApiDocumentation());
    }

    /**
     * Prepare a parameter
     *
     * @param mixed $value
     * @param string $paramName
     * @param ParameterValues $allValues
     * @return mixed
     */
    public function __invoke(mixed $value, string $paramName, ParameterValues $allValues): mixed
    {
        $rules = array_map(fn (ParameterValidationRule $r) => $r->getValidator(), $this->rules);

        $errorList = iterator_to_array($this->validator->validate($value, $rules));
        if (count($errorList) > 0) {
            throw InvalidValueException::fromMessages($this, $paramName, $value, array_map('strval', $errorList));
        } else {
            return $value;
        }
    }
}
