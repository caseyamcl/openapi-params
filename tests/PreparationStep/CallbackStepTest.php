<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/openapi-params
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\PreparationStep;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValues;

class CallbackStepTest extends TestCase
{
    public function testInvokeConvertsInvalidArgumentExceptionIntoInvalidParameterException()
    {
        $this->expectException(InvalidValueException::class);

        $step = new CallbackStep(function () {
            throw new InvalidArgumentException('Boo');
        }, 'test');

        $step->__invoke('test', 'test', new ParameterValues(['test']));
    }
}
