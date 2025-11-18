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

namespace OpenApiParams\Model;

use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\PreparationStep\CallbackStep;
use OpenApiParams\PreparationStep\EnumCheckStep;
use PHPUnit\Framework\TestCase;

class ParameterPreparationStepsTest extends TestCase
{
    public function testListSteps()
    {
        $this->assertContainsOnlyInstancesOf(PreparationStep::class, $this->getInstance()->listSteps());
    }

    public function testListNotes()
    {
        $notes = $this->getInstance()->listNotes();
        $this->assertSame(2, count($notes));
        $this->assertContainsOnlyString($notes);
    }

    public function testGetIterator()
    {
        $this->assertContainsOnlyInstancesOf(PreparationStep::class, $this->getInstance()->getIterator());
    }

    public function testCount()
    {
        $this->assertSame(2, count($this->getInstance()));
    }

    public function testWithStepAppendsStepToQueue()
    {
        $obj = $this->getInstance()->withStep(new CallbackStep('strtolower', 'Convert string back to lowercase'));
        $this->assertSame(3, count($obj));
    }

    /**
     * @return ParameterPreparationSteps
     */
    protected function getInstance(): ParameterPreparationSteps
    {
        return (new ParameterPreparationSteps())
            ->withStep(new EnumCheckStep(['foo', 'bar']))
            ->withStep(new CallbackStep('strtoupper', 'Convert string to upper-case'));
    }
}
