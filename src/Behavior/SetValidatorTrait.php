<?php
/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @package caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace Paramee\Behavior;

use InvalidArgumentException;
use Paramee\Model\ParameterValidationRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

/**
 * Trait SetValidatorTrait
 *
 * @package Paramee\Behavior
 */
trait SetValidatorTrait
{
    /**
     * @param $rule
     * @param string $documentation
     * @return ParameterValidationRule
     */
    protected function buildValidationRule($rule, string $documentation = ''): ParameterValidationRule
    {
        if ($rule instanceof ParameterValidationRule) {
            return $rule;
        } elseif ($rule instanceof Validatable) {
            return new ParameterValidationRule($rule, $documentation);
        } elseif (is_callable($rule)) {
            return new ParameterValidationRule(Validator::callback($rule), $documentation);
        } else {
            throw new InvalidArgumentException(sprintf(
                '%s::addValidation() expects callable or instance of one of the following: %s, %s',
                get_called_class(),
                ParameterValidationRule::class,
                Validatable::class
            ));
        }

    }

}