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
readonly class CallbackStep implements PreparationStep
{
    private Closure $step;
    private string $description;
    private ?string $documentation;

    public static function fromCallable(
        callable $callback,
        string $description,
        ?string $documentation = null
    ): CallbackStep {
        return new CallbackStep($callback, $description, $documentation);
    }

    /**
     * @param callable $callback Callback signature is ($value): mixed (throw \InvalidArgumentException if invalid)
     * @param string $description A description of the step; mainly for logging purposes
     * @param string|null $documentation Public-facing documentation for this step (if none necessary, return null)
     */
    public function __construct(callable $callback, string $description, ?string $documentation = null)
    {
        $this->step = $callback(...);
        $this->description = $description;
        $this->documentation = $documentation;
    }

    public function __toString(): string
    {
        return $this->description;
    }

    public function __invoke(mixed $value, string $paramName, ParameterValues $allValues): mixed
    {
        try {
            return call_user_func($this->step, $value);
        } catch (InvalidArgumentException $e) {
            throw InvalidValueException::fromMessage($this, $paramName, $value, $e->getMessage());
        }
    }

    public function getApiDocumentation(): ?string
    {
        return $this->documentation;
    }
}
