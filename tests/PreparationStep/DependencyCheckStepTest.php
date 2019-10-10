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

namespace OpenApiParams\PreparationStep;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValues;

class DependencyCheckStepTest extends TestCase
{
    public function testGetApiDocumentationReturnsExpectedMessageWhenModeIsMustExist()
    {
        $step = new DependencyCheckStep(['foo']);
        $this->assertStringContainsString(
            'only available if the other parameters are present:',
            $step->getApiDocumentation()
        );
    }

    public function testGetApiDocumentationReturnsExpectedMessageWhenModeIsMustNotExist()
    {
        $step = new DependencyCheckStep(['zurb'], DependencyCheckStep::MUST_NOT_EXIST);
        $this->assertStringContainsString(
            'not available if other parameters are present:',
            $step->getApiDocumentation()
        );
    }

    public function testMustExistSucceedsWhenOtherValuePresent()
    {
        $step = new DependencyCheckStep(['foo']);
        $result = $step->__invoke('buzz', 'buzz', $this->getAllValues());
        $this->assertSame('buzz', $result);
    }

    public function testMustExistFailsWhenOtherValueAbsent()
    {
        $this->expectException(InvalidValueException::class);
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
        $this->expectException(InvalidValueException::class);
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
        $this->expectException(InvalidValueException::class);

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
