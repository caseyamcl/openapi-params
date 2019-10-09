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

use Paramee\Contract\ParamFormat;
use Paramee\Model\Parameter;
use Paramee\Type\StringParameter;

/**
 * Class DateFormatTest
 * @package Paramee\Format
 */
class DateFormatTest extends DateTimeFormatTest
{
    public function dateTimeProvider(): array
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
