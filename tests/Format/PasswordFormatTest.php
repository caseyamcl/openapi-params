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

use OpenApiParams\Model\AbstractParamFormatTest;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Type\StringParameter;

class PasswordFormatTest extends AbstractParamFormatTest
{
    public function testGetValidationRulesReturnsEmptyArray(): void
    {
        $this->assertSame([], $this->getFormat()->getValidationRules());
    }

    /**
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
    {
        return new PasswordFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter())->setFormat(new PasswordFormat());
    }
}
