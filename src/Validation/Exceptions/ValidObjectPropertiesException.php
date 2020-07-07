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

namespace OpenApiParams\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class ValidObjectPropertiesException
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidObjectPropertiesException extends ValidationException
{
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} is missing required properties: {{missingProperties}}'
        ],
        self::MODE_NEGATIVE => [
            self::MODE_NEGATIVE => '{{name}} must not contain properties: {{missingProperties}}'
        ]
    ];
}
