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

use Paramee\Contract\ParamValidationRule;
use Respect\Validation\Validatable;

/**
 * Parameter Validation Rule
 *
 * Augments Respect rules by adding documentation for the rule
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class ParameterValidationRule implements ParamValidationRule
{
    /**
     * @var Validatable
     */
    private $rule;

    /**
     * @var string
     */
    private $description;
    /**
     * @var bool
     */
    private $includeDescriptionInApiDocumentation;

    /**
     * ParameterValidationRule constructor.
     * @param Validatable $rule
     * @param string $description
     * @param bool $includeDescriptionInDocumentation
     */
    public function __construct(
        Validatable $rule,
        string $description = '',
        bool $includeDescriptionInDocumentation = true
    ) {
        $this->rule = $rule;
        $this->description = $description;
        $this->includeDescriptionInApiDocumentation = $includeDescriptionInDocumentation;
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
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function includeInDocumentation(): bool
    {
        return $this->includeDescriptionInApiDocumentation;
    }
}
