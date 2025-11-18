<?php

/**
 *  OpenApi-Params Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/openapi-params
 *
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use OpenApiParams\Contract\PreparationStep;

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
     * @var array<int,PreparationStep>
     */
    private array $steps = [];

    /**
     * ParameterPreparationStepChain constructor.
     * @param iterable<int,PreparationStep> $steps
     */
    public function __construct(iterable $steps = [])
    {
        foreach ($steps as $step) {
            $this->add($step);
        }
    }

    /**
     * @return iterable<int,PreparationStep>
     */
    public function listSteps(): iterable
    {
        return $this->steps;
    }

    /**
     * @return array<int,string>
     */
    public function listNotes(): array
    {
        return array_map('strval', $this->steps);
    }

    /**
     * Append a step to the end of the preparation step stack and get a new copy of this object
     */
    public function withStep(PreparationStep $step): ParameterPreparationSteps
    {
        $that = clone $this;
        $that->add($step);
        return $that;
    }

    /**
     * Push a preparation step onto the end of the stack
     */
    private function add(PreparationStep $step): void
    {
        $this->steps[] = $step;
    }

    /**
     * @return ArrayIterator<int,PreparationStep>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->listSteps());
    }

    public function count(): int
    {
        return count($this->steps);
    }
}
