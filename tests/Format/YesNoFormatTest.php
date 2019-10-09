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

namespace Paramee\Format;

use Paramee\Model\AbstractParamFormatTest;
use Paramee\Contract\ParamFormat;
use Paramee\Exception\InvalidValueException;
use Paramee\Model\Parameter;
use Paramee\Type\StringParameter;

class YesNoFormatTest extends AbstractParamFormatTest
{
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

    public function invalidDataProvider(): array
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
    public function validDataProvider(): iterable
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
