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

use OpenApiParams\Model\ParameterValues;

/**
 * A preparation step for a parameter value
 *
 * These will be chained together to convert a 'raw value' into a 'prepared value'
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
interface PreparationStep
{
    /**
     * Get API Documentation for this step
     *
     * If this step defines a rule that is important to be included in the API description, then include
     * it here; e.g., "value must be ..."
     *
     * If no documentation is required for this rule, return NULL
     */
    public function getApiDocumentation(): ?string;

    /**
     * Describe what this step does (will appear in the debug log if enabled)
     */
    public function __toString(): string;

    /**
     * Prepare a parameter
     */
    public function __invoke(mixed $value, string $paramName, ParameterValues $allValues): mixed;
}
