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

namespace Paramee\PreparationStep;

use Paramee\Contract\PreparationStep;
use Paramee\Model\ParameterValues;

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

    /**
     * Get documentation for this preparation step to include parameter notes
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'sanitizes input using filter_far FILTER_SANITIZE_STRING';
    }

    /**
     * Prepare a parameter
     *
     * @param mixed $value
     * @param string $paramName
     * @param ParameterValues $allValues
     * @return mixed
     */
    public function __invoke($value, string $paramName, ParameterValues $allValues)
    {
        return filter_var(str_replace(["'", '"', "\\", '/'], '', strip_tags($value)), FILTER_SANITIZE_STRING);
    }

    /**
     * Get API Documentation for this step
     *
     * If this step defines a rule that is important to be included in the API documentation, then include
     * it here.  e.g. "value must be ..."
     *
     * @return string|null
     */
    public function getApiDocumentation(): ?string
    {
        return null;
    }
}
