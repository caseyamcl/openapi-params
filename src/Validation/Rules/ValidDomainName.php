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

namespace Paramee\Validation\Rules;

use Respect\Validation\Rules\OneOf;
use Respect\Validation\Validator;

/**
 * Class ValidDomainName
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidDomainName extends OneOf
{
    /**
     * ValidDomainName constructor.
     * @param bool $allowLocalhost
     */
    public function __construct(bool $allowLocalhost = true)
    {
        ($allowLocalhost)
            ? parent::__construct(Validator::domain(), Validator::equals('localhost'))
            : parent::__construct(Validator::domain());
    }
}
