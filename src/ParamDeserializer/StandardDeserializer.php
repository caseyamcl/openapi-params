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

namespace OpenApiParams\ParamDeserializer;

use InvalidArgumentException;
use OpenApiParams\Contract\ParameterDeserializer;
use OpenApiParams\Utility\UnpackCSV;
use stdClass;

/**
 * Class StandardDeserializer
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class StandardDeserializer implements ParameterDeserializer
{
    /**
     * Deserialize array value from string
     *
     * Example: "3,4,5" becomes ['3', '4', '5']
     *
     * @param string|array $value
     * @return array
     */
    public function deserializeArray($value): array
    {
        switch (true) {
            case is_array($value):
                return $value;
            case is_string($value):
                return UnpackCSV::un($value);
            default:
                throw new InvalidArgumentException('Cannot deserialize value (expected array or string');
        }
    }

    /**
     * Deserialize object value from a string
     *
     * Example string deserialization: 'role=admin,firstName=Alex' becomes { $role = 'admin; $firstName = 'Alex'; }
     *
     * @param string|stdClass|array $value
     * @return stdClass
     */
    public function deserializeObject($value): stdClass
    {
        switch (true) {
            case $value instanceof stdClass:
                return $value;
            case is_array($value):
                return (object) $value;
            case is_string($value):
                foreach (UnpackCSV::un($value) as $val) {
                    if (strpos($val, '=') !== false) {
                        list($k, $v) = explode('=', $val, 2);
                        $arr[$k] = $v;
                    } else {
                        throw new InvalidArgumentException('Cannot deserialize object; malformed string');
                    }
                }
                return (object) ($arr ?? []);
            default:
                throw new InvalidArgumentException('Cannot deserialize value (expected stdClass, array, or string');
        }
    }
}
