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

namespace Paramee\Utility;

use PHPUnit\Framework\TestCase;

/**
 * Class UnpackCSVTest
 * @package Paramee\Utility
 */
class UnpackCSVTest extends TestCase
{
    public function testUnpackWithDefaultSeparator()
    {
        $values = (new UnpackCSV())->unpack('test,test1,test2');
        $this->assertSame(['test', 'test1', 'test2'], $values);
    }

    public function testUnpackWithMultipleSeparators()
    {
        $unpacker = new UnpackCSV([',', '|']);
        $this->assertSame(['test', 'test1', 'test2'], $unpacker->unpack('test,test1|test2'));
    }

    public function testUnpackTrimsWhiteSpaceAndFiltersEmptyValues()
    {
        $values = (new UnpackCSV())->unpack(" test , test1\n,test2,,");
        $this->assertSame(['test', 'test1', 'test2'], $values);
    }

    public function testUn()
    {
        $values = UnpackCSV::un('test,test1,test2');
        $this->assertSame(['test', 'test1', 'test2'], $values);
    }

    public function testInvoke()
    {
        $values = (new UnpackCSV())->__invoke('test,test1,test2');
        $this->assertSame(['test', 'test1', 'test2'], $values);
    }

    public function testInvokeWithComplexExample()
    {
        $values = (new UnpackCSV())->__invoke('test , test1 |test2', [',', '|']);
        $this->assertSame(['test', 'test1', 'test2'], $values);
    }
}
