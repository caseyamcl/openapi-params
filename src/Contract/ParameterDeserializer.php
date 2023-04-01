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

use stdClass;

/**
 * Parameter Deserializer
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
interface ParameterDeserializer
{
    /**
     * Deserialize array value from string
     *
     * @param string|mixed $value
     * @return array
     */
    public function deserializeArray(mixed $value): array;

    /**
     * Deserialize object value from a string
     *
     * @param string|mixed $value  PHP may automatically do some processing for us (in the case of $_GET),
     *                             so we cannot specify a strict string type here.
     * @return stdClass
     */
    public function deserializeObject(mixed $value): stdClass;
}
