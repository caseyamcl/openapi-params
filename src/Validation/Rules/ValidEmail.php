<?php
/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/Paramee
 *  @package caseyamcl/Paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace Paramee\Validation\Rules;

use Egulias\EmailValidator\EmailValidator;
use Respect\Validation\Rules\Email;

class ValidEmail extends Email
{
    /**
     * @var bool
     */
    private $allowLocalhost;

    /**
     * ValidEmail constructor.
     * @param bool $allowLocalhost
     * @param EmailValidator|null $emailValidator
     */
    public function __construct(bool $allowLocalhost = true, ?EmailValidator $emailValidator = null)
    {
        parent::__construct($emailValidator);
        $this->allowLocalhost = $allowLocalhost;
    }

    public function validate($input)
    {
        if (parent::validate($input)) {
            return true;
        } else {
            list($local, $domain) = explode('@', $input, 2);
            return (strtolower($domain) === 'localhost') && parent::validate($local . '@example.org');
        }
    }
}
