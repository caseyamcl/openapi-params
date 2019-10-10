<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Paramee\Contract\PreparationStep;

/**
 * Parameter Preparation Steps
 *
 * Contains immutable preparation task queue for a parameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class ParameterPreparationSteps implements IteratorAggregate, Countable
{
    /**
     * @var PreparationStep[]
     */
    private $steps = [];

    /**
     * ParameterPreparationStepChain constructor.
     * @param iterable|PreparationStep[] $steps
     */
    public function __construct(iterable $steps = [])
    {
        foreach ($steps as $step) {
            $this->add($step);
        }
    }

    /**
     * @return iterable|PreparationStep[]
     */
    public function listSteps(): iterable
    {
        return $this->steps;
    }

    /**
     * @return array|string[]
     */
    public function listNotes(): array
    {
        return array_map('strval', $this->steps);
    }

    /**
     * Append a step to the end of the preparation step stack and get new copy of this object
     *
     * @param PreparationStep $step
     * @return ParameterPreparationSteps
     */
    public function withStep(PreparationStep $step): ParameterPreparationSteps
    {
        $that = clone $this;
        $that->add($step);
        return $that;
    }

    /**
     * Push a preparation step onto the end of the stack
     *
     * @param PreparationStep $step
     * @return ParameterPreparationSteps
     */
    private function add(PreparationStep $step): self
    {
        $this->steps[] = $step;
        return $this;
    }

    /**
     * @return ArrayIterator|PreparationStep[]
     */
    public function getIterator()
    {
        return new ArrayIterator($this->listSteps());
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->steps);
    }
}
