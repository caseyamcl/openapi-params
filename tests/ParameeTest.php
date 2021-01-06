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

namespace OpenApiParams;

use OpenApiParams\Model\ParameterList;
use OpenApiParams\Model\ParameterValuesContext;
use OpenApiParams\ParamContext\ParamBodyContext;
use OpenApiParams\ParamContext\ParamHeaderContext;
use OpenApiParams\ParamContext\ParamPathContext;
use OpenApiParams\ParamContext\ParamQueryContext;
use PHPUnit\Framework\TestCase;

class ParameeTest extends TestCase
{
    /**
     * @dataProvider dataValuesProvider
     * @param array $toCall
     * @param string $expectedClass
     */
    public function testFactoryMethods(array $toCall, string $expectedClass)
    {
        /** @var ParameterList $paramList */
        $paramList = call_user_func($toCall);
        $this->assertInstanceOf(ParameterValuesContext::class, $paramList->getContext());
        $this->assertSame($expectedClass, get_class($paramList->getContext()));
    }


    public function dataValuesProvider()
    {
        return [
            [[OpenApiParams::class, 'queryParams'], ParamQueryContext::class],
            [[OpenApiParams::class, 'headerParams'], ParamHeaderContext::class],
            [[OpenApiParams::class, 'bodyParams'], ParamBodyContext::class],
            [[OpenApiParams::class, 'pathParams'], ParamPathContext::class]
        ];
    }
}
