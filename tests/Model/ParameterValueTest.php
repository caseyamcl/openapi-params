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

class ParameterValueTest extends TestCase
{
    public function testIsPreparedReturnsFalseWhenNotPrepared()
    {
        $value = new ParameterValue('test', 'foo');
        $this->assertFalse($value->isPrepared());
    }

    public function testIsPreparedReturnsTrueWhenPrepared()
    {
        $value = (new ParameterValue('test', 'foo'))->withPreparedValue('FOO');
        $this->assertTrue($value->isPrepared());
    }

    public function testGetPreparedValueThrowsExceptionWhenNotPrepared()
    {
        $this->expectException(RuntimeException::class);
        $value = new ParameterValue('test', 'foo');
        $value->getPreparedValue();
    }

    public function testGetPreparedValueReturnsPreparedValue()
    {
        $value = (new ParameterValue('test', 'foo'))->withPreparedValue('FOO');
        $this->assertEquals('FOO', $value->getPreparedValue());
    }

    public function testGetName()
    {
        $value = (new ParameterValue('test', 'foo'));
        $this->assertEquals('test', $value->getName());
    }

    public function testGetRawValue()
    {
        $value = (new ParameterValue('test', 'foo'))->withPreparedValue('FOO');
        $this->assertEquals('foo', $value->getRawValue());
    }

    public function testValueIsImmutable()
    {
        $value = new ParameterValue('test', 'foo');
        $prepared = $value->withPreparedValue('FOO');

        $this->assertFalse($value->isPrepared());
        $this->assertTrue($prepared->isPrepared());
    }
}
