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

namespace Paramee;

use Paramee\Model\ParameterList;
use Paramee\Model\ParameterValuesContext;
use Paramee\ParamContext\ParamBodyContext;
use Paramee\ParamContext\ParamHeaderContext;
use Paramee\ParamContext\ParamPathContext;
use Paramee\ParamContext\ParamQueryContext;
use PHPUnit\Framework\TestCase;

class ParameeTest extends TestCase
{
    /**
     * @dataProvider dataValuesProvider
     * @param ParameterList $params
     * @param string $expectedClass
     */
    public function testFactoryMethods(ParameterList $params, string $expectedClass)
    {
        $this->assertInstanceOf(ParameterValuesContext::class, $params->getContext());
        $this->assertSame($expectedClass, get_class($params->getContext()));
    }


    public function dataValuesProvider()
    {
        return [
            [Paramee::queryParams(), ParamQueryContext::class],
            [Paramee::headerParams(), ParamHeaderContext::class],
            [Paramee::bodyParams(), ParamBodyContext::class],
            [Paramee::pathParams(), ParamPathContext::class]
        ];
    }
}
