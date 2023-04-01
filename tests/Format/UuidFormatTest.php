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

namespace OpenApiParams\Format;

use OpenApiParams\Model\AbstractParamFormatTestBase;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Type\StringParameter;

class UuidFormatTest extends AbstractParamFormatTestBase
{
    /**
     * @dataProvider validUuidDataProvider
     * @param string $value
     */
    public function testValidData(string $value): void
    {
        $this->assertSame($value, $this->getParameterWithFormat()->prepare($value));
    }

    public static function validUuidDataProvider(): array
    {
        return [
            ['726a1f97-154d-423d-9f5e-06ad9f4b8aed'],
            ['2b6afd54-24d4-408d-9c73-28126007add4'],
            ['d378495b-da78-44c8-b389-fe5bce22b9cc']
        ];
    }

    /**
     * @dataProvider invalidUuidDataProvider
     * @param string $value
     */
    public function testInvalidDataThrowsException(string $value): void
    {
        $this->expectException(InvalidValueException::class);
        $this->getParameterWithFormat()->prepare($value);
    }

    public static function invalidUuidDataProvider(): array
    {
        return [
            ['abc'],
            ['def'],
            ['123']
        ];
    }

    /**
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
    {
        return new UuidFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter())->setFormat(new UuidFormat());
    }
}
