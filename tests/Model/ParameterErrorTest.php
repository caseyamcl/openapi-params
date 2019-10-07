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

class ParameterErrorTest extends TestCase
{
    public function testGetPointerAddsInitialSlash()
    {
        $this->assertSame('/test', $this->getInstance()->getPointer());
    }

    public function testGetDetail()
    {
        $this->assertSame(
            'The problem was that you passed a string when you should have passed an integer',
            $this->getInstance()->getDetail()
        );
    }

    public function testGetCode()
    {
        $this->assertSame('500', $this->getInstance()->getCode());
    }

    public function testWithPointer()
    {
        $this->assertSame(
            '/data/attributes/test',
            $this->getInstance()->withPointer('data/attributes/test')->getPointer()
        );
    }

    public function testToStringReturnsTitle()
    {
        $this->assertSame('There was a problem', $this->getInstance()->__toString());
    }

    public function testGetTitle()
    {
        $this->assertSame('There was a problem', $this->getInstance()->getTitle());

    }

    public function testGetExtra()
    {
        $this->assertSame(['debug' => 'test', 'debug2' => 'abc'], $this->getInstance()->getExtra());
    }

    protected function getInstance(): ParameterError
    {
        return new ParameterError(
            'There was a problem',
            'test',
            'The problem was that you passed a string when you should have passed an integer',
            '500',
            ['debug' => 'test', 'debug2' => 'abc']
        );
    }
}
