<?php

/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 * @package caseyamcl/openapi-params
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\Validation\Rules;

use OpenApiParams\Validation\AbstractValidatorRuleTest;
use OpenApiParams\Validation\Exceptions\ValidUnixPathException;

class ValidUnixPathTest extends AbstractValidatorRuleTest
{
    /**
     * @dataProvider validUnixPathProvider
     */
    public function testValidateSucceedsWithValidValue(string $unixPath): void
    {
        $this->assertTrue((new ValidUnixPath())->validate($unixPath));
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
        $this->assertTrue((new ValidUnixPath(true))->validate('test/test'));
    }

    public function testValidateFailsWithRelativePathValue()
    {
        $this->expectException(ValidUnixPathException::class);
        (new ValidUnixPath(true))->assert('test\/test');
    }

    /**
     * @dataProvider invalidUnixPathProvider
     * @param string $unixPath
     */
    public function testValidateFailsWithInvalidValue(string $unixPath): void
    {
        $this->expectException(ValidUnixPathException::class);
        (new ValidUnixPath(true))->assert($unixPath);
    }

    public function invalidUnixPathProvider(): array
    {
        return [
            ['@'],
            ['123\/test']
        ];
    }
}
