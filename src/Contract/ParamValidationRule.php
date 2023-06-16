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

namespace OpenApiParams\Contract;

use Symfony\Component\Validator\Constraint;

/**
 * Parameter Validation Rule
 *
 * Augments Respect validation rule by adding OpenAPI documentation for the rule
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
interface ParamValidationRule
{
    /**
     * Get validator
     *
     * @return Constraint
     */
    public function getValidator(): Constraint;

    /**
     * Get the documentation for this validation rule
     *
     * @return string
     */
    public function getDescription(): string;
}
