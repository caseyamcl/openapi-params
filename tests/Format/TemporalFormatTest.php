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

use Carbon\CarbonImmutable;
use OpenApiParams\Model\AbstractParamFormatTestBase;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Type\StringParameter;

class TemporalFormatTest extends AbstractParamFormatTestBase
{
    /**
     * @dataProvider validValuesDataProvider
     * @param string $value
     */
    public function testFormatWithValidValues(string $value): void
    {
        $this->assertInstanceOf(CarbonImmutable::class, $this->getParameterWithFormat()->prepare($value));
    }

    /**
     * @dataProvider invalidValuesDataProvider
     * @param string $value
     */
    public function testFormatThrowsExceptionWithInvalidValues(string $value): void
    {
        $this->expectException(InvalidValueException::class);
        $this->getParameterWithFormat()->prepare($value);
    }

    public static function invalidValuesDataProvider(): array
    {
        return [
            ['foo'],
            ['bar']
        ];
    }

    public static function validValuesDataProvider(): array
    {
        return [
            ["now"],
            ["2017-03-04"],
            ["tomorrow"],
            ["2017-03-04T01:30:40Z"]
        ];
    }

    /**
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
    {
        return new TemporalFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter())->setFormat(new TemporalFormat());
    }
}
