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

namespace Paramee\PreparationStep;

use InvalidArgumentException;
use Paramee\Contract\PreparationStepInterface;
use Paramee\Exception\InvalidValueException;
use Paramee\Model\ParameterValues;

/**
 * Class DependencyCheckStep
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DependencyCheckStep implements PreparationStepInterface
{
    public const MUST_EXIST = true;
    public const MUST_NOT_EXIST = false;

    /**
     * @var array|string[]
     */
    private $params;

    /**
     * @var array|null
     */
    private $callbacks;

    /**
     * @var bool
     */
    private $mode;

    /**
     * DependencyCheckStep constructor.
     * @param array|string[] $params List of parameter names
     * @param bool $mode  Either TRUE (self::MUST_EXIST) or FALSE (self::MUST_NOT_EXIST)
     * @param array|null $callbacks  Keys are param names, values are callbacks
     */
    public function __construct(array $params, bool $mode = self::MUST_EXIST, ?array $callbacks = [])
    {
        $this->params = $params;
        $this->callbacks = $callbacks;
        $this->mode = $mode;
    }

    /**
     * Get API Documentation for this step
     *
     * If this step defines a rule that is important to be included in the API description, then include
     * it here.  e.g. "value must be ..."
     *
     * @return string|null
     */
    public function getApiDocumentation(): ?string
    {
        $template = $this->mode === self::MUST_EXIST
            ? 'only available if the other parameters are present: %s'
            : 'not available if other parameters are present: %s';

        return sprintf($template, implode(', ', $this->params));
    }

    /**
     * Describe what this step does (will appear in debug log if enabled)
     *
     * @return string
     */
    public function __toString(): string
    {
        $template = $this->mode === self::MUST_EXIST
            ? 'checks for presence of other parameters: %s'
            : 'checks for absence of other parameters: %s';

        return sprintf($template, implode(', ', $this->params));
    }

    /**
     * Prepare a parameter
     *
     * @param mixed $value The current value to be processed
     * @param string $paramName
     * @param ParameterValues $allValues All of the values
     * @return mixed
     */
    public function __invoke($value, string $paramName, ParameterValues $allValues)
    {
        // values must exist
        if ($this->mode === self::MUST_EXIST) {
            $template = '%s parameter can only be used when other parameter(s) are present: %s';
            $valid = count(array_diff($this->params, $allValues->listNames())) === 0;
        } else { // values must not exist
            $template = '%s parameter can not be used when other parameter(s) are present: %s';
            $valid = array_diff($this->params, $allValues->listNames()) == $this->params;
        }

        if (! $valid) {
            throw InvalidValueException::fromMessage($this, $paramName, $value, sprintf(
                $template,
                $paramName,
                implode(', ', $this->params)
            ));
        }

        // Also run callbacks
        foreach ($this->callbacks as $paramName => $callable) {
            try {
                if ($allValues->hasValue($paramName)) {
                    call_user_func($callable, $allValues->get($paramName));
                }
            } catch (InvalidArgumentException $e) {
                throw InvalidValueException::fromMessage($this, $paramName, $value, $e->getMessage());
            }
        }

        return $value;
    }
}
