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

declare(strict_types=1);

namespace Paramee\PreparationStep;

use InvalidArgumentException;
use Paramee\Contract\PreparationStepInterface;
use Paramee\Exception\InvalidParameterException;
use Paramee\Model\ParameterValues;

/**
 * Callback Parameter Preparation Step
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class CallbackStep implements PreparationStepInterface
{
    /**
     * @var callable
     */
    private $step;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string|null
     */
    private $documentation;

    /**
     * CallbackStep constructor.
     *
     * @param callable $callback Callback signature is ($value): mixed (throw \InvalidArgumentException if invalid)
     * @param string $description A description of the step
     * @param string|null $documentation
     */
    public function __construct(callable $callback, string $description, string $documentation = null)
    {
        $this->step = $callback;
        $this->description = $description;
        $this->documentation = $documentation;
    }

    /**
     * Get documentation for this preparation step to include parameter notes
     *
     * @return string
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
    public function __invoke($value, string $paramName, ParameterValues $allValues)
    {
        try {
            return call_user_func($this->step, $value);
        } catch (InvalidArgumentException $e) {
            throw InvalidParameterException::fromMessage($this, $paramName, $value, $e->getMessage());
        }
    }

    /**
     * Get API Documentation for this step
     *
     * If this step defines a rule that is important to be included in the API documentation, then include
     * it here.  e.g. "value must be ..."
     *
     * @return string|null
     */
    public function getApiDocumentation(): ?string
    {
        return $this->documentation;
    }
}
