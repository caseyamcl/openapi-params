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

use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Type\StringParameter;

/**
 * Class DateFormatTest
 * @package OpenApi-Params\Format
 */
class DateFormatTest extends DateTimeFormatTest
{
    public static function dateTimeProvider(): array
    {
        return [
            ['2002-10-02'],
            [DateFormat::DATE_FORMAT_EXAMPLE]
        ];
    }

    /**
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
    {
        return new DateFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter('test'))->setFormat(new DateFormat());
    }
}
