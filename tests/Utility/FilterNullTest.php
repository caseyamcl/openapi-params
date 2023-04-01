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

namespace OpenApiParams\Utility;

use PHPUnit\Framework\TestCase;

class FilterNullTest extends TestCase
{
    /**
     * @dataProvider valuesProvider
     * @param array $values
     * @param array $expected
     */
    public function testFilterNull(array $values, array $expected)
    {
        $this->assertSame(array_values(FilterNull::filterNull($values)), array_values($expected));
    }

    public static function valuesProvider(): array
    {
        return [
            [['a', null, 'b', null],  ['a', 'b']],
            [['a', '', 'b'], ['a', '', 'b']],
            [['a', false, 'b'], ['a', false, 'b']]
        ];
    }

    public function testKeysArePreserved()
    {
        $arr = [
            'a' => 'A',
            'b' => null,
            'c' => 'C'
        ];

        $this->assertSame(FilterNull::filterNull($arr), ['a' => 'A', 'c' => 'C']);
    }
}
