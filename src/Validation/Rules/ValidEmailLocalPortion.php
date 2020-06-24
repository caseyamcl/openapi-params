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

namespace OpenApiParams\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Rules\Email;

/**
 * Class ValidEmailLocal
 *
 * Attempts to check if a string is a valid email local part (everything before the '@')
 * See: https://en.wikipedia.org/wiki/Email_address#Local-part
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidEmailLocalPortion extends AbstractRule
{
    public function validate($input): bool
    {
        return (new Email())->validate($input . '@example.org');
    }
}
