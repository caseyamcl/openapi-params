<?php

/**
 *  OpenApi-Params Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/openapi-params
 *  @package caseyamcl/openapi-params
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\Format;

use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Model\AbstractParamFormatTestBase;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Type\StringParameter;

/**
 * Class AlphanumericFormatTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class AlphanumericFormatTest extends AbstractParamFormatTestBase
{
    public function testValidValueIsPrepared()
    {
        $param = StringParameter::create('test')->setFormat(new AlphanumericFormat('_'));
        $prepared = $param->prepare('abc_ghi');
        $this->assertEquals('abc_ghi', $prepared);
    }

    public function testNonValidValueThrowsException()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('is not valid');
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
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
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
