<?php
/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace Paramee\Format;

use Paramee\Contract\ParamFormatInterface;
use Paramee\AbstractParamFormatTest;
use Paramee\Exception\InvalidParameterException;
use Paramee\Model\Parameter;
use Paramee\Type\StringParameter;

/**
 * Class AlphanumericFormatTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class AlphanumericFormatTest extends AbstractParamFormatTest
{
    public function testValidValueIsPrepared()
    {
        $param = StringParameter::create('test')->setFormat(new AlphanumericFormat('_'));
        $prepared = $param->prepare('abc_ghi');
        $this->assertEquals('abc_ghi', $prepared);
    }

    public function testNonValidValueThrowsException()
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('RespectValidationStep');
        $this->getParameterWithFormat()->prepare('___');
    }

    public function testSetExtraCharsClobbersExtraChars()
    {
        $format = new AlphanumericFormat();
        $param = StringParameter::create('test')->setFormat($format);
        $format->setExtraChars('_@');
        $this->assertEquals('abc_@def', $param->prepare('abc_@def'));
    }

    /**
     * @return ParamFormatInterface
     */
    protected function getFormat(): ParamFormatInterface
    {
        return new AlphanumericFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter('test'))->setFormat($this->getFormat());
    }
}
