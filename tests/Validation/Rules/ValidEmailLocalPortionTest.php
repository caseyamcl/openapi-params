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

use Paramee\Validation\Exceptions\ValidEmailLocalPortionException;
use PHPUnit\Framework\TestCase;

class ValidEmailLocalPortionTest extends TestCase
{
    public function testInvalidLocalPartThrowsException()
    {
        $this->expectException(ValidEmailLocalPortionException::class);
        (new ValidEmailLocalPortion())->assert('john..doe');
    }

    public function testValidLocalPartSucceeds()
    {
        $this->assertTrue((new ValidEmailLocalPortion())->assert('john.doe'));
    }
}
