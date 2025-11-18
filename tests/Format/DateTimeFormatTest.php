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
use Exception;
use OpenApiParams\Model\AbstractParamFormatTestBase;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Type\StringParameter;
use PHPUnit\Framework\Attributes\DataProvider;

class DateTimeFormatTest extends AbstractParamFormatTestBase
{
    #[DataProvider('dateTimeProvider')]
    public function testValidDatesArePreparedCorrectly(string $dtString): void
    {
        $param = $this->getParameterWithFormat();
        $date = $param->prepare($dtString);
        $this->assertInstanceOf(CarbonImmutable::class, $date);

        $format = current(constant(get_class($this->getFormat()) . '::VALID_FORMATS'));

        $this->assertSame((new CarbonImmutable($dtString))->format($format), $date->format($format));
    }

    public static function dateTimeProvider(): array
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
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('invalid data');
        $param = $this->getParameterWithFormat();
        $param->prepare('foobar');
    }

    #[DataProvider('dateTimeProvider')]
    public function testEarliestDateWithValidDates(string $date): void
    {
        /** @var DateTimeFormat $format */
        $format = $this->getFormat();
        $carbonObj = $format->buildDate($date);
        $format->setEarliestDate($carbonObj);
        $param = (new StringParameter())->setFormat($format);
        $this->assertEquals(new CarbonImmutable($date), $param->prepare($date));
    }

    #[DataProvider('dateTimeProvider')]
    public function testEarliestDateWithInvalidDates(string $date): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('invalid data');

        /** @var DateTimeFormat $format */
        $format = $this->getFormat();
        $carbonObj = $format->buildDate($date);
        $format->setEarliestDate($carbonObj->addSecond());
        $param = (new StringParameter())->setFormat($format);
        $param->prepare($date);
    }

    #[DataProvider('dateTimeProvider')]
    public function testLatestDateWithValidDates(string $date): void
    {
        /** @var DateTimeFormat $format */
        $format = $this->getFormat();
        $carbonObj = $format->buildDate($date);
        $format->setLatestDate($carbonObj);
        $param = (new StringParameter())->setFormat($format);
        $this->assertEquals(new CarbonImmutable($date), $param->prepare($date));
    }

    #[DataProvider('dateTimeProvider')]
    public function testLatestDateWithInvalidDates(string $date): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('invalid data');

        /** @var DateTimeFormat $format */
        $format = $this->getFormat();
        $carbonObj = $format->buildDate($date);
        $format->setLatestDate($carbonObj->subSecond());
        $param = (new StringParameter())->setFormat($format);
        $param->prepare($date);
    }

    protected function getFormat(): ParamFormat
    {
        return new DateTimeFormat();
    }

    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter('test'))->setFormat(new DateTimeFormat());
    }
}
