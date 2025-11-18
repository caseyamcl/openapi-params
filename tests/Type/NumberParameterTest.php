<?php

/**
 *  OpenApi-Params Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/openapi-params
 *  @package caseyamcl/openapi-params
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\Type;

use OpenApiParams\Model\AbstractNumericParameterTestBase;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\PreparationStep\EnsureCorrectDataTypeStep;

/**
 * Class NumberParameterTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class NumberParameterTest extends AbstractNumericParameterTestBase
{
    /**
     * Test that, if we are not requiring a decimal, integers are automatically typecast (even if typecast is disabled)
     */
    public function testIntegerTypeCastToFloatOrDoubleWhenDecimalNotRequired()
    {
        $obj = $this->buildInstance()
            ->setAllowTypeCast(false)
            ->setRequireDecimal(false);

        $this->assertSame($this->cast(5.0), $obj->prepare(5));
    }

    public function testIntegerTypeNotFailsWhenDecimalRequired()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(EnsureCorrectDataTypeStep::class);

        $obj = $this->buildInstance()
            ->setAllowTypeCast(false)
            ->setRequireDecimal(true);

        $obj->prepare(5);
    }

    public function testIsRequireDecimalTrue()
    {
        $obj = $this->buildInstance()->setRequireDecimal(true);
        $this->assertTrue($obj->isRequireDecimal());
        $this->assertInstanceOf(ParamFormat::class, $obj->getFormat());
    }

    public function testIsRequireDecimalFalse()
    {
        $obj = $this->buildInstance()->setRequireDecimal(false);
        $this->assertFalse($obj->isRequireDecimal());
        $this->assertNull($obj->getFormat());
    }

    public function testIntegerAllowedWhenRequireDecimalIsFalse()
    {
        $obj = $this->buildInstance()->setRequireDecimal(false);
        $this->assertEquals(25, $obj->prepare(25));
    }

    public function testIntegerDisallowedWhenRequireDecimalIsTrue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('invalid data type');
        $obj = $this->buildInstance()->setRequireDecimal(true);
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
    protected static function getTwoOrMoreValidValues(): array
    {
        // Techincally, integers are allowed, too, but we return only floats here, because the default
        // getInstance() returns an object that has strict decimal checking enabled.
        return [25.4, 30.0, -402.6, 70.5];
    }


    /**
     * Return values that are not the correct type but can be automatically type-cast if that is enabled
     *
     * @return array  Values for type cast check
     */
    protected static function getValuesForTypeCastTest(): array
    {
        return ['7', '9.0'];
    }

    /**
     * @param string $name
     * @return NumberParameter
     */
    protected function buildInstance(string $name = 'test'): Parameter
    {
        return new NumberParameter($name);
    }

    protected function cast(int|float $value): float
    {
        return (PHP_FLOAT_DIG >= 15) ? doubleval($value) : floatval($value);
    }
}
