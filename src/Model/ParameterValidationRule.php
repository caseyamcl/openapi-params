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

namespace Paramee\Model;

use Respect\Validation\Validatable;
use Paramee\Contract\ParameterValidationRuleInterface;

/**
 * Parameter Validation Rule
 *
 * Augments Respect rules by adding documentation for the rule
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ParameterValidationRule implements ParameterValidationRuleInterface
{
    /**
     * @var Validatable
     */
    private $rule;

    /**
     * @var string
     */
    private $documentation;

    /**
     * ParameterValidationRule constructor.
     * @param Validatable $rule
     * @param string $documentation
     */
    public function __construct(Validatable $rule, string $documentation = '')
    {
        $this->rule = $rule;
        $this->documentation = $documentation;
    }

    /**
     * Get validator
     *
     * @return Validatable
     */
    public function getValidator(): Validatable
    {
        return $this->rule;
    }

    /**
     * Get the documentation for this validation rule
     *
     * @return string
     */
    public function getDocumentation(): string
    {
        return $this->documentation;
    }
}
