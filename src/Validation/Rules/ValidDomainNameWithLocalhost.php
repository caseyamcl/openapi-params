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
use Respect\Validation\Validator;

/**
 * Class ValidDomainName
 *
 * Attempts to check if a string is a valid domain; the difference between this and the built-in Domain check
 * is that this one allows 'localhost'
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidDomainNameWithLocalhost extends AbstractRule
{
    private bool $allowLocalhost;
    
    public function __construct(bool $allowLocalhost = true)
    {
        $this->allowLocalhost = $allowLocalhost;
    }

    public function validate($input): bool
    {
        $validator = ($this->allowLocalhost)
            ? Validator::oneOf(Validator::domain(false), Validator::equals('localhost'))
            : Validator::domain(! $this->allowLocalhost);

        return $validator->validate($input);
    }
}
