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
use Paramee\Model\Parameter;
use Paramee\Type\StringParameter;

class BinaryFormatTest extends AbstractParamFormatTest
{
    /**
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
    {
        return new BinaryFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter('test'))->setFormat(new BinaryFormat());
    }
}
