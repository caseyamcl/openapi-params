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

namespace Paramee\PreparationStep;

use PHPUnit\Framework\TestCase;
use stdClass;

class EnumCheckStepTest extends TestCase
{
    /**
     * @dataProvider valuesDataProvider
     * @param array $value
     * @param string $expected
     */
    public function testToStringPrintsExpectedMessages(array $value, string $expected): void
    {
        $expected = 'check raw typecast value against allowed values: ' . $expected;
        $this->assertSame($expected, (new EnumCheckStep($value))->__toString());
    }

    public function valuesDataProvider(): iterable
    {
        yield [['a', 'b'], 'a, b'];
        yield [['a'], 'a'];
        yield [[], '(empty set)'];
        $obj = new stdClass();
        $obj->a = 'b';
        $obj->c = 'd';
        yield [[$obj], 'b, d'];
    }

    public function testGetApiDocumentationReturnsNull(): void
    {
        $this->assertNull((new EnumCheckStep(['a', 'b']))->getApiDocumentation());
    }
}
