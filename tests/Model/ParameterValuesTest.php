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

namespace Paramee\Model;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class ParameterValuesTest extends TestCase
{

    public function testCount()
    {
        $obj = new ParameterValues(['foo' => 'bar', 'baz' => 'biz']);
        $this->assertEquals(2, $obj->count());
    }

    public function testGetContext()
    {
        $obj = new ParameterValues(['foo' => 'bar', 'baz' => 'biz']);
        $this->assertInstanceOf(ParameterValuesContext::class, $obj->getContext());
    }

    public function testGetPreparedValueWhenValueIsPrepared()
    {
        $obj = (new ParameterValues(['foo' => 'bar', 'baz' => 'biz']))
            ->withPreparedValue('foo', 'bAr')  // Simulate step 1
            ->withPreparedValue('foo', 'BAR'); // Simulate step 2

        $this->assertEquals('BAR', $obj->getPreparedValue('foo'));
    }

    public function testGetPreparedValueThrowsExceptionWhenValueIsNotPrepared()
    {
        $this->expectException(RuntimeException::class);
        $obj = (new ParameterValues(['foo' => 'bar', 'baz' => 'biz']));
        $obj->getPreparedValue('foo');
    }


    public function testGetRawValue()
    {
        $obj = (new ParameterValues(['foo' => 'bar', 'baz' => 'biz']))
            ->withPreparedValue('foo', 'bAr')  // Simulate step 1
            ->withPreparedValue('foo', 'BAR'); // Simulate step 2

        $this->assertEquals('bar', $obj->getRawValue('foo'));
        $this->assertEquals('biz', $obj->getRawValue('baz'));
    }

    public function testHasValue()
    {
        $obj = (new ParameterValues(['foo' => 'bar', 'baz' => 'biz']));
        $this->assertTrue($obj->hasValue('foo'));
        $this->assertFalse($obj->hasValue('buzz'));
    }

    public function testGetIterator()
    {
        $obj = (new ParameterValues(['foo' => 'bar', 'baz' => 'biz']));
        $this->assertContainsOnlyInstancesOf(ParameterValue::class, $obj);
    }

    public function testPreparationWorksCorrectlyWithIterator()
    {
        $allValues = new ParameterValues(['foo' => 'bar']);

        $callbacks = [
            function () {
                return 'Bar';
            },
            function () {
                return 'BAR';
            }
        ];

        foreach ($callbacks as $cb) {
            $allValues = $allValues->withPreparedValue('foo', $cb());
        }

        $this->assertEquals('BAR', $allValues->getPreparedValue('foo'));
    }
}
