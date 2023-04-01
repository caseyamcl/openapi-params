<?php

/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\Utility;

/**
 * Class FilterNull
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class FilterNull
{
    /**
     * Filter NULL values out of an array (with strict type-checking)
     */
    public static function filterNull(array $arr): array
    {
        return array_filter($arr, function ($value) {
            return $value !== null;
        });
    }
}
