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

use Carbon\CarbonImmutable;
use Paramee\AbstractParamFormatTest;
use Paramee\Contract\ParamFormatInterface;
use Paramee\Exception\InvalidValueException;
use Paramee\Model\Parameter;
use Paramee\Type\StringParameter;

class TemporalFormatTest extends AbstractParamFormatTest
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

    public function invalidValuesDataProvider(): array
    {
        return [
            ['foo'],
            ['bar'],
            ['1234.23']
        ];
    }

    public function validValuesDataProvider(): array
    {
        return [
            ["now"],
            ["2017-03-04"],
            ["tomorrow"],
            ["2017-03-04T01:30:40Z"]
        ];
    }

    /**
     * @return ParamFormatInterface
     */
    protected function getFormat(): ParamFormatInterface
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
