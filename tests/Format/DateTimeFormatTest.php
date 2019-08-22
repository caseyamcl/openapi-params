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
use Exception;
use Paramee\AbstractParamFormatTest;
use Paramee\Contract\ParamFormatInterface;
use Paramee\Exception\InvalidParameterException;
use Paramee\Model\Parameter;
use Paramee\Type\StringParameter;

class DateTimeFormatTest extends AbstractParamFormatTest
{
    /**
     * @param string $dtString
     * @dataProvider dateTimeProvider
     * @throws Exception
     */
    public function testValidDatesArePreparedCorrectly(string $dtString): void
    {
        $param = $this->getParameterWithFormat();
        $date = $param->prepare($dtString);
        $this->assertInstanceOf(CarbonImmutable::class, $date);

        $format = current(constant(get_class($this->getFormat()) . '::VALID_FORMATS'));

        $this->assertSame((new CarbonImmutable($dtString))->format($format), $date->format($format));
    }

    public function dateTimeProvider(): array
    {
        return [
            ['2002-10-02T10:00:00-05:00'],
            ['2002-10-02T15:00:00Z'],
            ['2002-10-02T15:00:00.654Z'],
            [DateTimeFormat::DATE_FORMAT_EXAMPLE]
        ];
    }

    public function testInvalidDatesThrowException()
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('invalid data');
        $param = $this->getParameterWithFormat();
        $param->prepare('foobar');
    }

    /**
     * @return ParamFormatInterface
     */
    protected function getFormat(): ParamFormatInterface
    {
        return new DateTimeFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter('test'))->setFormat(new DateTimeFormat());
    }
}
