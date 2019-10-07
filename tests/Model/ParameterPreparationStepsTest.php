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

namespace Paramee\Model;

use Paramee\Contract\PreparationStepInterface;
use Paramee\PreparationStep\CallbackStep;
use Paramee\PreparationStep\EnumCheckStep;
use PHPUnit\Framework\TestCase;

class ParameterPreparationStepsTest extends TestCase
{

    public function testListSteps()
    {
        $this->assertContainsOnlyInstancesOf(PreparationStepInterface::class, $this->getInstance()->listSteps());
    }

    public function testListNotes()
    {
        $notes = $this->getInstance()->listNotes();
        $this->assertSame(2, count($notes));
        $this->assertContainsOnly('string', $notes);
    }

    public function testGetIterator()
    {
        $this->assertContainsOnlyInstancesOf(PreparationStepInterface::class, $this->getInstance()->getIterator());
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
