<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
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
use PHPUnit\Framework\TestCase;

class StandardDeserializerTest extends TestCase
{
    /**
     * @dataProvider validArraysProvider
     * @param $val
     */
    public function testDeserializeArrayWithValidData($val)
    {
        $this->assertEquals([1, 2, 3], (new StandardDeserializer())->deserializeArray($val));
    }

    public function validArraysProvider(): array
    {
        return [
            [[1, 2, 3]],
            [['1', '2', '3']],
            ['1,2,3'],
            [',1,2,3,']
        ];
    }

    /**
     * @dataProvider validObjectsProvider
     * @param $val
     */
    public function testDeserializeObjectWithValidData($val)
    {
        $this->assertEquals(
            (object) ['name' => 'Bob', 'age' => 25],
            (new StandardDeserializer())->deserializeObject($val)
        );
    }

    public function validObjectsProvider(): array
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
