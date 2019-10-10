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

namespace Paramee\Behavior;

use DateTime;
use Paramee\Model\ParameterList;
use Paramee\Model\ParameterValues;
use Paramee\ParamContext\ParamQueryContext;
use Paramee\PreparationStep\CallbackStep;
use PHPUnit\Framework\TestCase;

class ConvenienceMethodsTraitTest extends TestCase
{

    public function testAddCsvValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addCsv('test');
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

    public function testAddUuidValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addUuid('test');
        $prepared = $obj->prepare(['test' => 'e0959969-28d9-4572-9bf6-f970e4e9696e']);
        $this->assertSame('e0959969-28d9-4572-9bf6-f970e4e9696e', $prepared->getPreparedValue('test'));
    }

    public function testAddInteger(): void
    {
        $obj = new ParameterList('test');
        $obj->addInteger('test');
        $prepared = $obj->prepare(['test' => 12]);
        $this->assertSame(12, $prepared->getPreparedValue('test'));
    }

    public function testAddYesNoValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addYesNo('test');
        $prepared = $obj->prepare(['test' => 'on']);
        $this->assertSame(true, $prepared->getPreparedValue('test'));
    }

    public function testAddDateValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addDate('test');
        $prepared = $obj->prepare(['test' => '2019-05-12']);
        $this->assertSame('2019-05-12', $prepared->getPreparedValue('test')->format('Y-m-d'));
    }

    public function testAddBinaryValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addBinary('test');
        $prepared = $obj->prepare(['test' => '011011']);
        $this->assertSame('011011', $prepared->getPreparedValue('test'));
    }

    public function testAddDateTimeValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addDateTime('test');
        $prepared = $obj->prepare(['test' => '2017-07-21T17:32:28Z']);
        $this->assertSame(
            '2017-07-21T17:32:28+00:00',
            $prepared->getPreparedValue('test')->format(DateTime::RFC3339)
        );
    }


    public function testAddBooleanValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addBoolean('test');
        $prepared = $obj->prepare(['test' => true]);
        $this->assertSame(true, $prepared->getPreparedValue('test'));
    }

    public function testAddByteValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addByte('test');
        $prepared = $obj->prepare(['test' => base64_encode('test')]);
        $this->assertSame('test', $prepared->getPreparedValue('test'));
    }

    public function testAddAlphaNumericValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addAlphaNumeric('test');
        $prepared = $obj->prepare(['test' => 'abc123']);
        $this->assertSame('abc123', $prepared->getPreparedValue('test'));
    }

    public function testAddArrayValue(): void
    {
        $obj = new ParameterList('test');
        $param = $obj->addArray('test');
        $param->setUniqueItems(true);
        $allValues = new ParameterValues(['test' => 'a=apple,b=banana'], new ParamQueryContext());

        $param->addPreparationStep(new CallbackStep(function (array $value) {
            return array_map('strtoupper', $value);
        }, 'convert to uppercase'));
        $this->assertSame(['A', 'B', 'C'], $param->prepare('a,b,c', $allValues));
    }

    public function testAddObjectValue(): void
    {
        $obj = new ParameterList('test', []);

        $allValues = new ParameterValues(['test' => 'a=apple,b=banana'], new ParamQueryContext());

        $param = $obj->addObject('test');
        $this->assertEquals(
            (object) ['a' => 'apple', 'b' => 'banana'],
            $param->prepare('a=apple,b=banana', $allValues)
        );
    }

    public function testAddPasswordValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addPassword('test');
        $this->assertSame('test', $obj->prepare(['test' => 'test'])->getPreparedValue('test'));
    }

    public function testAddStringValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addString('test');
        $this->assertSame('test', $obj->prepare(['test' => 'test'])->getPreparedValue('test'));
    }

    public function testAddEmailValue(): void
    {
        $obj = new ParameterList('test');
        $obj->addEmail('test');
        $this->assertSame(
            'test@example.org',
            $obj->prepare(['test' => 'test@example.org'])->getPreparedValue('test')
        );
    }
}
