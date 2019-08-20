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

namespace Paramee\PreparationStep;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Paramee\Exception\InvalidParameterException;
use Paramee\Model\ParameterValues;

class CallbackStepTest extends TestCase
{
    public function testInvokeConvertsInvalidArgumentExceptionIntoInvalidParameterException()
    {
        $this->expectException(InvalidParameterException::class);

        $step = new CallbackStep(function () {
            throw new InvalidArgumentException('Boo');
        }, 'test');

        $step->__invoke('test', 'test', new ParameterValues(['test']));
    }
}
