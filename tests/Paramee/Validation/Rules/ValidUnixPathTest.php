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

use Paramee\Validation\Exceptions\ValidUnixPathException;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Validator;

class ValidUnixPathTest extends TestCase
{
    /**
     * @dataProvider validUnixPathProvider
     */
    public function testValidateSucceedsWithValidValue(string $unixPath): void
    {
        $this->assertTrue((new ValidUnixPath())->assert($unixPath));
    }

    public function validUnixPathProvider(): array
    {
        return [
            ['/test'],
            ['/123'],
            ['/test 123']
        ];
    }

    public function testValidateSucceedsWithRelativePathValue()
    {
        $this->assertTrue((new ValidUnixPath(true))->assert('test/test'));
    }

    public function testValidateFailsWithRelativePathValue()
    {
        $this->expectException(ValidUnixPathException::class);
        (new ValidUnixPath(true))->assert('test\/test');
    }

    /**
     * @dataProvider invalidUnixPathProvider
     * @throws ComponentException
     */
    public function testValidateFailsWithInvalidValue(string $unixPath): void
    {
        $this->expectException(ValidUnixPathException::class);
        Validator::buildRule(new ValidUnixPath())->assert($unixPath);
    }

    public function invalidUnixPathProvider(): array
    {
        return [
            ['@'],
            ['test/test'],
            ['123\/test']
        ];
    }
}
