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

namespace OpenApiParams\PreparationStep;

use Closure;
use InvalidArgumentException;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValues;

/**
 * Callback Parameter Preparation Step
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class CallbackStep implements PreparationStep
{
    private Closure $step;
    private string $description;
    private ?string $documentation;

    public static function fromCallable(callable $callback, string $description, ?string $documentation = null): static
    {
        return new static($callback, $description, $documentation);
    }

    /**
     * CallbackStep constructor.
     *
     * @param callable $callback Callback signature is ($value): mixed (throw \InvalidArgumentException if invalid)
     * @param string $description A description of the step
     * @param string|null $documentation
     */
    public function __construct(callable $callback, string $description, ?string $documentation = null)
    {
        $this->step = $callback(...);
        $this->description = $description;
        $this->documentation = $documentation;
    }

    /**
     * Get documentation for this preparation step to include parameter notes
     */
    public function __toString(): string
    {
        return $this->description;
    }

    /**
     * Prepare a parameter
     *
     * @param mixed $value
     * @param string $paramName
     * @param ParameterValues $allValues
     * @return mixed
     */
    public function __invoke(mixed $value, string $paramName, ParameterValues $allValues): mixed
    {
        try {
            return call_user_func($this->step, $value);
        } catch (InvalidArgumentException $e) {
            throw InvalidValueException::fromMessage($this, $paramName, $value, $e->getMessage());
        }
    }

    /**
     * Get API Documentation for this step
     *
     * If this step defines a rule that is important to be included in the API documentation, then include
     * it here.  e.g. "value must be ..."
     */
    public function getApiDocumentation(): ?string
    {
        return $this->documentation;
    }
}
