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

use InvalidArgumentException;
use OpenApiParams\Model\ParameterValidationRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

/**
 * Trait SetValidatorTrait
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
trait SetValidatorTrait
{
    /**
     * Build an openapi-params validation rule from an existing rule, respect/validation rule, or callback
     */
    protected function buildValidationRule($rule, string $documentation = ''): ParameterValidationRule
    {
        return match (true) {
            $rule instanceof ParameterValidationRule => $rule,
            $rule instanceof Validatable => new ParameterValidationRule($rule, $documentation),
            is_callable($rule) => new ParameterValidationRule(Validator::callback($rule), $documentation)
        };
    }
}
