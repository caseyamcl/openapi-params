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
use RuntimeException;
use Paramee\Exception\InvalidParameterException;
use Paramee\Model\ParameterValues;

class DependencyCheckStepTest extends TestCase
{
    public function testMustExistSucceedsWhenOtherValuePresent()
    {
        $step = new DependencyCheckStep(['foo']);
        $result = $step->__invoke('buzz', 'buzz', $this->getAllValues());
        $this->assertSame('buzz', $result);
    }

    public function testMustExistFailsWhenOtherValueAbsent()
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('buzz parameter can only be used when other parameter(s) are present: zurb');

        $step = new DependencyCheckStep(['zurb']);
        $step->__invoke('buzz', 'buzz', $this->getAllValues());
    }

    public function testMustNotExistSucceedsWhenOtherValueAbsent()
    {
        $step = new DependencyCheckStep(['zurb'], DependencyCheckStep::MUST_NOT_EXIST);
        $result = $step->__invoke('buzz', 'buzz', $this->getAllValues());
        $this->assertSame('buzz', $result);
    }

    public function testMustNotExistFailsWhenOtherValuePresent()
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('buzz parameter can not be used when other parameter(s) are present: foo');

        $step = new DependencyCheckStep(['foo'], DependencyCheckStep::MUST_NOT_EXIST);
        $step->__invoke('buzz', 'buzz', $this->getAllValues());
    }

    public function testCallbacksRunIfParametersArePresent()
    {
        $iRan = false;

        $callback = function () use (&$iRan): void {
            $iRan = true;
        };

        $step = new DependencyCheckStep([], DependencyCheckStep::MUST_EXIST, ['foo' => $callback]);
        $step->__invoke('buzz', 'buzz', $this->getAllValues());
        $this->assertTrue($iRan);
    }

    public function testCallbacksAreSkippedIfParametersAreNotPresent()
    {
        $iRan = false;

        $callback = function () use (&$iRan): void {
            $iRan = true;
        };

        $step = new DependencyCheckStep([], DependencyCheckStep::MUST_EXIST, ['larz' => $callback]);
        $step->__invoke('buzz', 'buzz', $this->getAllValues());
        $this->assertFalse($iRan);
    }

    public function testCallbackInvalidArgumentExceptionIsConvertedToInvalidParameterException()
    {
        $this->expectException(InvalidParameterException::class);

        $callback = function (): void {
            throw new InvalidArgumentException('I am an exception');
        };

        $step = new DependencyCheckStep([], DependencyCheckStep::MUST_EXIST, ['foo' => $callback]);
        $step->__invoke('buzz', 'buzz', $this->getAllValues());
    }

    public function testCallbackOtherExceptionIsNotCaught()
    {
        $this->expectException(RuntimeException::class);

        $callback = function (): void {
            throw new RuntimeException('I am an exception');
        };

        $step = new DependencyCheckStep([], DependencyCheckStep::MUST_EXIST, ['foo' => $callback]);
        $step->__invoke('buzz', 'buzz', $this->getAllValues());
    }

    /**
     * @return ParameterValues
     */
    protected function getAllValues(): ParameterValues
    {
        return new ParameterValues([
            'foo'  => 'bar',
            'baz'  => 'biz',
            'zab'  => 'zub',
            'buzz' => 'buzz'
        ]);
    }
}
