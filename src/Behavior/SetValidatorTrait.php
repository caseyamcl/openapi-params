<?php

/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\Behavior;

use OpenApiParams\Model\ParameterValidationRule;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * Trait SetValidatorTrait
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
trait SetValidatorTrait
{
    /**
     * Build an openapi-params validation rule from an existing rule, Symfony Validator rule, or callback
     */
    protected function buildValidationRule(
        ParameterValidationRule|Constraint|callable $rule,
        string $documentation = ''
    ): ParameterValidationRule {
        return match (true) {
            $rule instanceof ParameterValidationRule => $rule,
            $rule instanceof Constraint => new ParameterValidationRule($rule, $documentation),
            is_callable($rule) => new ParameterValidationRule(new Callback($rule), $documentation)
        };
    }
}
