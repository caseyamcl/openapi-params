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
use Paramee\Exception\InvalidValueException;
use Paramee\Model\ParameterValues;
use Paramee\Type\IntegerParameter;
use Paramee\Type\StringParameter;

class ArrayItemsPreparationStepTest extends TestCase
{
    public function testValuesAreReturnedAsIsWhenToParametersOrForEachIsSet()
    {
        $items = ['string', 25.45, 100, false];

        $step = new ArrayItemsPreparationStep();
        $this->assertEquals($items, $step->__invoke($items, 'test', new ParameterValues([$items])));
    }

    public function testForEachRunsWhenNoMappingButForeachSpecified()
    {
        $foreach = [
            new CallbackStep(function ($value) {
                return strval($value);
            }, 'cast to string'),
            new CallbackStep(function ($value) {
                return strtoupper($value);
            }, 'to uppercase')
        ];

        $step = new ArrayItemsPreparationStep(ArrayItemsPreparationStep::ALL, $foreach);

        $items    = ['string', 25.45, 100];
        $expected = ['STRING', '25.45', '100'];

        $this->assertSame($expected, $step($items, 'test', new ParameterValues([$items])));
    }

    public function testInvokeThrowsInvalidArgumentExceptionWhenArrayNotPassed()
    {
        $this->expectException(InvalidArgumentException::class);
        $step = new ArrayItemsPreparationStep();

        /** @noinspection PhpParamsInspection */
        $step->__invoke('not-array', 'test', new ParameterValues(['not-array']));
    }

    public function testInvokeFindsFirstWorkingParameterWhenMoreThanOnePerTypeIsDefined()
    {
        $step = new ArrayItemsPreparationStep([
            'string' => [
                (new StringParameter())->setPattern('^[a-z]+$'),
                (new StringParameter())->setPattern('/^[0-9]+$/')
            ]
        ]);

        $this->assertSame(['xx'], $step->__invoke(['xx'], 'test', new ParameterValues(['23'])));
    }

    public function testInvokeThrowsExpectedExceptionWhenMorThanOneParameterPerTypeIsDefinedAndNoneMatch()
    {
        $step = new ArrayItemsPreparationStep([
            'string' => [
                (new StringParameter())->setPattern('^[a-z]+$'),
                (new StringParameter())->setPattern('/^[0-9]+$/')
            ]
        ]);

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('type mis-match or type constraints failed');
        $step->__invoke(['XX'], 'test', new ParameterValues(['XX']));
    }

    public function testForEachIsRunAfterWhenMultipleParameterRulesAreSet()
    {
        $step = new ArrayItemsPreparationStep([
            'string' => [
                (new StringParameter())->setPattern('^[a-z]+$')->setTrim(true),
                (new StringParameter())->setPattern('/^[0-9]+$/')->setTrim(true)
            ]
        ], [
            new CallbackStep('strtoupper', 'convert to uppercase')
        ]);

        $this->assertSame(['XX'], $step->__invoke(['xx'], 'test', new ParameterValues(['xx'])));
    }

    public function testForEachIsRunAfterWhenSingleParameterRuleIsSet()
    {
        $step = new ArrayItemsPreparationStep(
            ['string' => [(new StringParameter())->setPattern('^[a-z]+$')->setTrim(true)]],
            [new CallbackStep('strtoupper', 'convert to uppercase')]
        );

        $this->assertSame(['XX'], $step->__invoke(['xx'], 'test', new ParameterValues(['xx'])));
    }

    public function testNonExplicitlyMappedSingleParameterAllowsTypeCast()
    {
        $step = new ArrayItemsPreparationStep(
            ['integer' => [IntegerParameter::create()->setAllowTypeCast(true)]]
        );

        $prepared = $step->__invoke(['35'], 'test', ParameterValues::single(['35']));
        $this->assertSame([35], $prepared);
    }

    public function testInvalidTypeThrowsAppropriateException()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('could not map to parameter');
        // Resources are invalid
        $fh = fopen('php://temp', 'r');
        $items = [$fh];

        $step = new ArrayItemsPreparationStep();
        $step->__invoke($items, 'test', new ParameterValues([$items]));
    }
}
