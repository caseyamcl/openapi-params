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

namespace OpenApiParams\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class ValidEmailLocalPortionException
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidEmailLocalPartException extends ValidationException
{
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be a valid local portion of email'
        ],
        self::MODE_NEGATIVE => [
            self::MODE_NEGATIVE => '{{name}} must not be a local portion of email'
        ]
    ];
}
