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

namespace Paramee\Contract;

use Respect\Validation\Validatable;

/**
 * Parameter Validation Rule
 *
 * Augments Respect validation rule by adding OpenAPI documentation for the rule
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
interface ParameterValidationRuleInterface
{
    /**
     * Get validator
     *
     * @return Validatable
     */
    public function getValidator(): Validatable;

    /**
     * Get the documentation for this validation rule
     *
     * @return string
     */
    public function getDocumentation(): string;
}
