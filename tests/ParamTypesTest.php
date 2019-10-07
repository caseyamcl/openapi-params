<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * Class ParamTypesTest
 */
class ParamTypesTest extends TestCase
{
    public function testResolveTypeInstanceForValueThrowsExceptionForUnrecognizedType()
    {
        $this->expectException(RuntimeException::class);
        $fh = fopen('php://temp', 'r'); // there is no type mapped to a resource
        ParamTypes::resolveParameterForValue($fh, 'test');
    }

    /**
     * @param $value
     * @param string $expectedType
     * @dataProvider valueProvider
     */
    public function testResolveParameterForValue($value, string $expectedType)
    {
        $value = ParamTypes::resolveParameterForValue($value, 'test');
        $this->assertSame($value->getTypeName(), $expectedType);
    }

    public function valueProvider()
    {
        return [
            [new stdClass(), ParamTypes::OBJECT],
            [['a', 'b', 'c'], ParamTypes::ARRAY],
            [3, ParamTypes::INTEGER],
            [3.0, ParamTypes::NUMBER],
            [false, ParamTypes::BOOLEAN],
            ['abc', ParamTypes::STRING]
        ];
    }
}
