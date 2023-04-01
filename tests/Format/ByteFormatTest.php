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

use OpenApiParams\Model\AbstractParamFormatTestBase;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Type\StringParameter;

/**
 * Byte Format is defined in the OpenApi Docs as a base64-encoded value
 * See: https://swagger.io/docs/specification/data-models/data-types/#string
 *
 * @package OpenApi-Params\Format
 */
class ByteFormatTest extends AbstractParamFormatTestBase
{
    public function testValidValueIsPrepared(): void
    {
        $param = $this->getParameterWithFormat();
        $prepared = $param->prepare('dGVzdA=='); // value is 'test'
        $this->assertSame('test', $prepared);
    }

    public function testNonBase64EncodedStringThrowsException(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('invalid data');
        $param = $this->getParameterWithFormat();
        $param->prepare('@@@@');
    }

    public function testNonBase64EncodedStringWithValidCharactersThrowsException(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('invalid data');
        $param = $this->getParameterWithFormat();
        $param->prepare('a');
    }

    /**
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
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
