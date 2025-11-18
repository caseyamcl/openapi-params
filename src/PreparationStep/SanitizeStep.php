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

use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Model\ParameterValues;

/**
 * Sanitize step using FILTER_SANITIZE_STRING
 *
 * This step is built into the AbstractParameter, so if your parameter extends
 * that class, it will be run automatically.
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class SanitizeStep implements PreparationStep
{
    public function __toString(): string
    {
        return 'sanitizes input using filter_var FILTER_SANITIZE_SPECIAL_CHARS';
    }

    /**
     * Prepare a parameter
     */
    public function __invoke(mixed $value, string $paramName, ParameterValues $allValues): mixed
    {
        return filter_var(str_replace(["'", '"', "\\", '/'], '', strip_tags($value)), FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * No documentation necessary for this step
     */
    public function getApiDocumentation(): ?string
    {
        return null;
    }
}
