<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/Paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/Paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace Paramee\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class ValidUuidException
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidUuidException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be a valid UUID'
        ],
        self::MODE_NEGATIVE => [
            self::MODE_NEGATIVE => '{{name}} must not be a UUID'
        ]
    ];
}
