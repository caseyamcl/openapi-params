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

namespace Paramee;

use Paramee\Exception\InvalidValueException;
use Paramee\Model\AbstractNumericParameter;
use Paramee\PreparationStep\RespectValidationStep;

/**
 * Class AbstractNumericParameterTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 * @method AbstractNumericParameter getInstance(string $name = 'test')
 */
abstract class AbstractNumericParameterTest extends AbstractParameterTest
{
    public function testGetMultipleOf()
    {
        $obj = $this->getInstance()->setMultipleOf((int) 5);
        $this->assertEquals(5, $obj->getMultipleOf());
    }

    public function testSetMultipleOf()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(RespectValidationStep::class);

        $obj = $this->getInstance()->setMultipleOf(5);
        $obj->prepare($this->cast(17));
    }

    public function testGetMinimum()
    {
        $obj = $this->getInstance()->setMinimum(-5);
        $this->assertEquals($this->cast(-5), $obj->getMinimum());
    }

    public function testMin()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(RespectValidationStep::class);
        $obj = $this->getInstance()->min(-5);
        $obj->prepare($this->cast(-22));
    }

    public function testMax()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(RespectValidationStep::class);
        $obj = $this->getInstance()->max(22);
        $obj->prepare($this->cast(55));
    }

    public function testSetMinimumWithInvalidValue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(RespectValidationStep::class);
        $obj = $this->getInstance()->setMinimum(-5);
        $obj->prepare($this->cast(-22));
    }

    public function testGetMaximum()
    {
        $obj = $this->getInstance()->setMinimum(22);
        $this->assertEquals(22, $obj->getMinimum());
    }

    public function testSetMaximumWithInvalidValue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(RespectValidationStep::class);
        $obj = $this->getInstance()->setMaximum(22);
        $obj->prepare($this->cast(55));
    }

    public function testSetExclusiveMaximum()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(RespectValidationStep::class);
        $obj = $this->getInstance()->setMaximum(22)->setExclusiveMaximum(true);
        $obj->prepare($this->cast(22));
    }

    public function testSetExclusiveMinimum()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(RespectValidationStep::class);
        $obj = $this->getInstance()->setMinimum(-5)->setExclusiveMinimum(true);
        $obj->prepare($this->cast(-5));
    }

    public function testIsExclusiveMaximum()
    {
        $obj = $this->getInstance()->setMinimum(-5)->setExclusiveMaximum(true);
        $this->assertTrue($obj->isExclusiveMaximum());
    }

    public function testIsExclusiveMinimum()
    {
        $obj = $this->getInstance()->setMinimum(-5)->setExclusiveMinimum(true);
        $this->assertTrue($obj->isExclusiveMinimum());
    }

    /**
     * @param int $value
     * @return double|float|int
     */
    abstract protected function cast(int $value);
}
