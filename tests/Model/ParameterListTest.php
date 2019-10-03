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

namespace Paramee\Model;

use PHPUnit\Framework\TestCase;

class ParameterListTest extends TestCase
{
    public function testConstructor(): void
    {
        $obj = new ParameterList('test');
        $this->assertInstanceOf(ParameterList::class, $obj);
    }

    public function testAddCsvValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addCsvValue('test');
        $prepared = $obj->prepare(['test' => 'a,b,c']);
        $this->assertEquals(['a', 'b', 'c'], $prepared->getPreparedValue('test'));
    }

    public function testAddNumber(): void
    {
        $obj = new ParameterList('test');
        $obj->addNumber('test')->setAllowTypeCast(true);
        $prepared = $obj->prepare(['test' => '25.2']);
        $this->assertSame(25.2, $prepared->getPreparedValue('test'));
    }

    public function testGetIterator(): void
    {

    }

    public function testAddUuidValue(): void
    {

    }

    public function testAddInteger(): void
    {

    }

    public function testAddYesNoValue(): void
    {

    }

    public function testAddDateValue(): void
    {

    }

    public function testGetParameters(): void
    {

    }

    public function testAddBinaryValue(): void
    {

    }

    public function testAddDateTimeValue(): void
    {

    }

    public function testGetContext(): void
    {

    }

    public function testCount(): void
    {

    }

    public function testPrepareWithDefaults(): void
    {

    }

    public function testPrepareWithUndefinedValuesAndStrictIsTrue(): void
    {

    }

    public function testPrepareWithUndefinedValuesAndStrictIsFalse(): void
    {

    }

    public function testPrepareWithMissingRequiredValues(): void
    {

    }

    public function testGetApiDocumentationReturnsEmptyArrayWhenNoParametersAreAdded(): void
    {

    }

    public function testGetApiDocumentationReturnsExpectedValuesWhenParametersAreAdded(): void
    {

    }

    public function testAddBoolean(): void
    {

    }

    public function testAddByteValue(): void
    {

    }

    public function testAddAlphaNumericValue(): void
    {

    }

    public function testAddArray(): void
    {

    }

    public function testAddObject(): void
    {

    }

    public function testAddPasswordValue(): void
    {

    }

    public function testAdd(): void
    {

    }

    public function testAddString(): void
    {

    }

    public function testGetName(): void
    {

    }
}
