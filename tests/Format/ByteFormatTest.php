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

use Paramee\AbstractParamFormatTest;
use Paramee\Contract\ParamFormatInterface;
use Paramee\Exception\InvalidValueException;
use Paramee\Model\Parameter;
use Paramee\Type\StringParameter;

/**
 * Byte Format is defined in the OpenApi Docs as a base64-encoded value
 * See: https://swagger.io/docs/specification/data-models/data-types/#string
 *
 * @package Paramee\Format
 */
class ByteFormatTest extends AbstractParamFormatTest
{
    public function testValidValueIsPrepared()
    {
        $param = $this->getParameterWithFormat();
        $prepared = $param->prepare('dGVzdA=='); // value is 'test'
        $this->assertSame('test', $prepared);
    }

    public function testNonBase64EncodedStringThrowsException()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('invalid data');
        $param = $this->getParameterWithFormat();
        $param->prepare('@@@@');
    }

    /**
     * @return ParamFormatInterface
     */
    protected function getFormat(): ParamFormatInterface
    {
        return new ByteFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter('test'))->setFormat($this->getFormat());
    }
}
