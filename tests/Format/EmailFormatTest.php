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
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\AbstractParamFormatTestBase;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Type\StringParameter;

class EmailFormatTest extends AbstractParamFormatTestBase
{
    public function testValidValueIsPrepared()
    {
        $prepared = $this->getParameterWithFormat()->prepare('test@example.org');
        $this->assertSame('test@example.org', $prepared);
    }

    public function testInvalidValueThrowsException()
    {
        // local part of email cannot contain two dots (..)
        $this->expectException(InvalidValueException::class);
        $this->getParameterWithFormat()->prepare('test..abc@example.org');
    }

    /**
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
    {
        return new EmailFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter())->setFormat($this->getFormat());
    }
}
