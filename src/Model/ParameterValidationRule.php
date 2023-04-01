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

namespace OpenApiParams\Model;

use OpenApiParams\Contract\ParamValidationRule;
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
    private Validatable $rule;
    private string $description;
    private bool $includeDescriptionInApiDocumentation;

    /**
     * ParameterValidationRule constructor.
     */
    public function __construct(
        Validatable $rule,
        string $description,
        bool $includeDescriptionInDocumentation = true
    ) {
        $this->rule = $rule;
        $this->description = $description;
        $this->includeDescriptionInApiDocumentation = $includeDescriptionInDocumentation;
    }

    /**
     * Get validator
     */
    public function getValidator(): Validatable
    {
        return $this->rule;
    }

    /**
     * Get the documentation for this validation rule
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function includeInDocumentation(): bool
    {
        return $this->includeDescriptionInApiDocumentation;
    }
}
