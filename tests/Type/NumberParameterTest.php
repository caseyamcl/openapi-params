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

namespace Paramee\Type;

use Paramee\Model\AbstractNumericParameterTest;
use Paramee\Contract\ParamFormatInterface;
use Paramee\Exception\InvalidValueException;
use Paramee\Model\Parameter;
use Paramee\PreparationStep\EnsureCorrectDataTypeStep;

/**
 * Class NumberParameterTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class NumberParameterTest extends AbstractNumericParameterTest
{
    /**
     * Test that, if we are not requiring a decimal, integers are automatically typecast (even if typecast is disabled)
     */
//    public function testIntegerTypeCastToFloatOrDoubleWhenDecimalNotRequired()
//    {
//        $obj = $this->getInstance('test')
//            ->setAllowTypeCast(false)
//            ->setRequireDecimal(false);
//
//        $this->assertSame($this->cast(5.0), $obj->prepare(5));
//    }

    public function testIntegerTypeNotFailsWhenDecimalRequired()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(EnsureCorrectDataTypeStep::class);

        $obj = $this->getInstance()
            ->setAllowTypeCast(false)
            ->setRequireDecimal(true);

        $obj->prepare(5);
    }

    public function testIsRequireDecimalTrue()
    {
        $obj = $this->getInstance()->setRequireDecimal(true);
        $this->assertTrue($obj->isRequireDecimal());
        $this->assertInstanceOf(ParamFormatInterface::class, $obj->getFormat());
    }

    public function testIsRequireDecimalFalse()
    {
        $obj = $this->getInstance()->setRequireDecimal(false);
        $this->assertFalse($obj->isRequireDecimal());
        $this->assertNull($obj->getFormat());
    }

    public function testIntegerAllowedWhenRequireDecimalIsFalse()
    {
        $obj = $this->getInstance()->setRequireDecimal(false);
        $this->assertEquals(25, $obj->prepare(25));
    }

    public function testIntegerDisallowedWhenRequireDecimalIsTrue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('invalid data type');
        $obj = $this->getInstance()->setRequireDecimal(true);
        $obj->prepare(25);
    }

    public function testIntegerAllowedByDefault()
    {
        $obj = new NumberParameter('test');
        $this->assertEquals(25, $obj->prepare(25));
    }

    /**
     * @return array
     */
    protected function getTwoOrMoreValidValues(): array
    {
        // Techincally, integers are allowed, too, but we return only floats here, because the default
        // getInstance() returns an object that has strict decimal checking enabled.
        return [25.4, 30.0, -402.6, 70.5];
    }


    /**
     * Return values that are not the correct type, but can be automatically type-cast if that is enabled
     *
     * @return array|mixed[]  Values for type cast check
     */
    protected function getValuesForTypeCastTest(): array
    {
        return ['7', '9.0'];
    }

    /**
     * @param string $name
     * @return NumberParameter
     */
    protected function getInstance(string $name = 'test'): Parameter
    {
        return new NumberParameter($name, false, true);
    }

    /**
     * @param int $value
     * @return double|float
     */
    protected function cast(int $value)
    {
        return (PHP_FLOAT_DIG >= 15) ? doubleval($value) : floatval($value);
    }
}
