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

use Paramee\Validation\Exceptions\ValidUuidException;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Validator;

class ValidUuidTest extends TestCase
{
    /**
     * @dataProvider validUuidDataProvider
     */
    public function testValidateSucceedsWithValidValue(string $uuid): void
    {
        $this->assertTrue((new ValidUuid())->assert($uuid));
    }

    public function validUuidDataProvider(): array
    {
        return [
            ['726a1f97-154d-423d-9f5e-06ad9f4b8aed'],
            ['2b6afd54-24d4-408d-9c73-28126007add4'],
            ['d378495b-da78-44c8-b389-fe5bce22b9cc']
        ];
    }

    /**
     * @dataProvider invalidUuidDataProvider
     * @throws ComponentException
     */
    public function testValidateFailsWithInvalidValue(string $uuid): void
    {
        $this->expectException(ValidUuidException::class);
        Validator::buildRule(new ValidUuid())->assert($uuid);
    }

    public function invalidUuidDataProvider(): array
    {
        return [
            ['abc'],
            ['def'],
            ['123']
        ];
    }
}
