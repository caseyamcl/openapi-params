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

declare(strict_types=1);

namespace OpenApiParams\Format;

use OpenApiParams\Model\AbstractParamFormatTestBase;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Type\StringParameter;

class YesNoFormatTest extends AbstractParamFormatTestBase
{
    public function testValidList(): void
    {
        $this->assertEquals(0, 0);
    }

    /**
     * @dataProvider validDataProvider
     * @param string $value
     * @param bool $expected
     */
    public function testPrepareUsingValidData(string $value, bool $expected): void
    {
        $this->assertSame($expected, $this->getParameterWithFormat()->prepare($value));
    }

    /**
     * @dataProvider invalidDataProvider
     * @param string $value
     */
    public function testPrepareUsingInvalidData(string $value): void
    {
        $this->expectException(InvalidValueException::class);
        $this->getParameterWithFormat()->prepare($value);
    }

    public static function invalidDataProvider(): array
    {
        return [
            ['foo'],
            ['bar'],
            ['baz'],
            ['-1']
        ];
    }

    /**
     * @return iterable
     */
    public static function validDataProvider(): iterable
    {
        foreach (YesNoFormat::BOOLEAN_MAP as $key => $value) {
            yield [(string) $key, $value];
        }
    }

    /**
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
    {
        return new YesNoFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter())->setFormat(new YesNoFormat());
    }
}
