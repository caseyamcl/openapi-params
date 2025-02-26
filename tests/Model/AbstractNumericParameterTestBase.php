<?php

/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 * @package caseyamcl/openapi-params
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\Model;

use OpenApiParams\AbstractParameterTestBase;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\PreparationStep\ValidationStep;

/**
 * Class AbstractNumericParameterTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 * @method AbstractNumericParameter buildInstance(string $name = 'test')
 */
abstract class AbstractNumericParameterTestBase extends AbstractParameterTestBase
{
    public function testGetMultipleOf()
    {
        $obj = $this->buildInstance()->setMultipleOf((int) 5);
        $this->assertEquals(5, $obj->getMultipleOf());
    }

    public function testSetMultipleOf()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);

        $obj = $this->buildInstance()->setMultipleOf(5);
        $obj->prepare($this->cast(17));
    }

    public function testGetMinimum()
    {
        $obj = $this->buildInstance()->setMinimum(-5);
        $this->assertEquals($this->cast(-5), $obj->getMinimum());
    }

    public function testMin()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);
        $obj = $this->buildInstance()->min(-5);
        $obj->prepare($this->cast(-22));
    }

    public function testMax()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);
        $obj = $this->buildInstance()->max(22);
        $obj->prepare($this->cast(55));
    }

    public function testSetMinimumWithInvalidValue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);
        $obj = $this->buildInstance()->setMinimum(-5);
        $obj->prepare($this->cast(-22));
    }

    public function testGetMaximum()
    {
        $obj = $this->buildInstance()->setMinimum(22);
        $this->assertEquals(22, $obj->getMinimum());
    }

    public function testSetMaximumWithInvalidValue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);
        $obj = $this->buildInstance()->setMaximum(22);
        $obj->prepare($this->cast(55));
    }

    public function testSetExclusiveMaximum()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);
        $obj = $this->buildInstance()->setMaximum(22)->setExclusiveMaximum(true);
        $obj->prepare($this->cast(22));
    }

    public function testSetExclusiveMinimum()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);
        $obj = $this->buildInstance()->setMinimum(-5)->setExclusiveMinimum(true);
        $obj->prepare($this->cast(-5));
    }

    public function testIsExclusiveMaximum()
    {
        $obj = $this->buildInstance()->setMinimum(-5)->setExclusiveMaximum(true);
        $this->assertTrue($obj->isExclusiveMaximum());
    }

    public function testIsExclusiveMinimum()
    {
        $obj = $this->buildInstance()->setMinimum(-5)->setExclusiveMinimum(true);
        $this->assertTrue($obj->isExclusiveMinimum());
    }

    /**
     * @param int $value
     * @return float|int
     */
    abstract protected function cast(int $value): float|int;
}
