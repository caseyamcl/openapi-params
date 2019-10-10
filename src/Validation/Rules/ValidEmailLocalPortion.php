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

use Respect\Validation\Rules\Email;

/**
 * Class ValidEmailLocal
 *
 * See: https://en.wikipedia.org/wiki/Email_address#Local-part
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidEmailLocalPortion extends Email
{
    /**
     * @param string $input
     * @return bool
     */
    public function validate($input)
    {
        return parent::validate($input . '@example.org');
    }
}
