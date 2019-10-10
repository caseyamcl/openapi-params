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
 * Allow NULL value decorator for parameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class AllowNullPreparationStep implements PreparationStep
{
    /**
     * @var PreparationStep
     */
    private $innerStep;

    public function __construct(PreparationStep $innerStep)
    {
        $this->innerStep = $innerStep;
    }

    /**
     * Get API Documentation for this step
     *
     * If this step defines a rule that is important to be included in the API description, then include
     * it here.  e.g. "value must be ..."
     *
     * @return string|null
     */
    public function getApiDocumentation(): ?string
    {
        return $this->innerStep->getApiDocumentation();
    }

    /**
     * Describe what this step does (will appear in debug log if enabled)
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->innerStep->__toString() . ' (if value is not NULL)';
    }

    /**
     * Prepare a parameter
     *
     * @param mixed $value The current value to be processed
     * @param string $paramName
     * @param ParameterValues $allValues All of the values
     * @return mixed
     */
    public function __invoke($value, string $paramName, ParameterValues $allValues)
    {
        return $value === null ? $value : $this->innerStep->__invoke($value, $paramName, $allValues);
    }
}
