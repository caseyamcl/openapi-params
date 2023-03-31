<?php

/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 * @package caseyamcl/openapi-params
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\Contract;

use OpenApiParams\Model\ParameterError;

interface ParameterException
{
    /**
     * Get parameter errors
     *
     * @return array|ParameterError[]
     */
    public function getErrors(): array;

    /**
     * Get parameter errors with added pointer prefix
     *
     * @param string $pointerPrefix
     * @return array|ParameterError[]
     */
    public function getErrorsWithPointerPrefix(string $pointerPrefix): array;
}
