<?php
/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @package caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace Paramee\Validation\Rules;

use Paramee\Validation\Exceptions\ValidDomainNameException;
use PHPUnit\Framework\TestCase;

class ValidDomainNameTest extends TestCase
{
    public function testLocalhostThrowsExceptionUnlessEnabledInConstructor()
    {
        $this->expectException(ValidDomainNameException::class);
        (new ValidDomainName(false))->assert('localhost');
    }

    public function testLocalhostWorksWhenExplicitlyEnabledInConstructor()
    {
        $this->assertTrue((new ValidDomainName(true))->assert('localhost'));
    }
}
