<?php

/**
 *  OpenApi-Params Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/openapi-params
 *  @package caseyamcl/openapi-params
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\ParamDeserializer;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StandardDeserializerTest extends TestCase
{
    #[DataProvider('validArraysProvider')]
    public function testDeserializeArrayWithValidData($val)
    {
        $this->assertEquals([1, 2, 3], (new StandardDeserializer())->deserializeArray($val));
    }

    public static function validArraysProvider(): array
    {
        return [
            [[1, 2, 3]],
            [['1', '2', '3']],
            ['1,2,3'],
            [',1,2,3,']
        ];
    }

    #[DataProvider('validObjectsProvider')]
    public function testDeserializeObjectWithValidData($val)
    {
        $this->assertEquals(
            (object) ['name' => 'Bob', 'age' => 25],
            (new StandardDeserializer())->deserializeObject($val)
        );
    }

    public static function validObjectsProvider(): array
    {
        return [
            [['name' => 'Bob', 'age' => 25]],
            [(object) ['name' => 'Bob', 'age' => 25]],
            ['name=Bob,age=25']
        ];
    }

    public function testDeserializeObjectThrowsExceptionOnInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        (new StandardDeserializer())->deserializeObject(22); // int is invalid type
    }

    public function testDeserializeArrayThrowsExceptionOnInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        (new StandardDeserializer())->deserializeArray(22); // int is invalid type
    }

    public function testGarbledStringThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        (new StandardDeserializer())->deserializeObject('something@not'); // does not contain '='
    }
}
