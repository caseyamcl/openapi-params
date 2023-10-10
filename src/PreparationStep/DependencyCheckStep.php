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

use InvalidArgumentException;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValues;
use Webmozart\Assert\Assert;

/**
 * Class DependencyCheckStep
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DependencyCheckStep implements PreparationStep
{
    public const MUST_NOT_EXIST = 0;
    public const MUST_EXIST = 1;
    public const MAY_EXIST = 2;

    /**
     * @var array<int,string>
     */
    private array $paramNames;
    private ?array $callbacks = [];
    private int $mode;

    /**
     * DependencyCheckStep constructor.
     * @param array|string[] $params List of parameter names
     * @param int $mode  Either 1 (self::MUST_EXIST). 0 (self::MUST_NOT_EXIST), or 2 (self::MAY_EXIST)
     * @param array|null $callbacks  Keys are param names, values are callbacks
     */
    public function __construct(array $params, int $mode = self::MUST_EXIST, ?array $callbacks = [])
    {
        Assert::inArray($mode, (new \ReflectionClass($this))->getConstants());

        $this->paramNames = $params;
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

        return sprintf($template, implode(', ', $this->paramNames));
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

        return sprintf($template, implode(', ', $this->paramNames));
    }

    /**
     * Prepare a parameter
     *
     * @param mixed $value The current value to be processed
     * @param string $paramName
     * @param ParameterValues $allValues All the values
     * @return mixed
     */
    public function __invoke(mixed $value, string $paramName, ParameterValues $allValues): mixed
    {
        // values must exist
        switch ($this->mode) {
            case self::MUST_EXIST:
                $template = '%s parameter can only be used when other parameter(s) are present: %s';
                $valid = count(array_diff($this->paramNames, $allValues->listNames())) === 0;
                break;
            case self::MUST_NOT_EXIST:
                $template = '%s parameter can not be used when other parameter(s) are present: %s';
                $valid = array_diff($this->paramNames, $allValues->listNames()) == $this->paramNames;
                break;
            case self::MAY_EXIST:
            default:
                $template = '';
                $valid = true;
        }

        if (! $valid) {
            throw InvalidValueException::fromMessage($this, $paramName, $value, sprintf(
                $template,
                $paramName,
                implode(', ', $this->paramNames)
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
