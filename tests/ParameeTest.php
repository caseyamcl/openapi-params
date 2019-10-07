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
            [[Paramee::class, 'queryParams'], ParamQueryContext::class],
            [[Paramee::class, 'headerParams'], ParamHeaderContext::class],
            [[Paramee::class, 'bodyParams'], ParamBodyContext::class],
            [[Paramee::class, 'pathParams'], ParamPathContext::class]
        ];
    }
}
