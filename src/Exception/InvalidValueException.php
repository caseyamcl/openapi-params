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

namespace Paramee\Exception;

use Paramee\Behavior\ParameterErrorsTrait;
use Paramee\Contract\ParameterException;
use Paramee\Contract\PreparationStepInterface;
use Paramee\Model\ParameterError;
use RuntimeException;
use Webmozart\Assert\Assert;

/**
 * This is thrown when parameter data is invalid.  This must be thrown from a PreparationStep
 *
 * It usually translates to 422, but can be 400 or whatever.
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class InvalidValueException extends RuntimeException implements ParameterException
{
    use ParameterErrorsTrait;

    /**
     * @var PreparationStepInterface
     */
    private $step;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Generate an InvalidValueException from a single error message
     *
     * @param PreparationStepInterface $step
     * @param string $paramName  Full parameter name
     * @param $value
     * @param string $message
     * @return InvalidValueException
     */
    public static function fromMessage(PreparationStepInterface $step, string $paramName, $value, string $message)
    {
        return static::fromMessages($step, $paramName, $value, [$message]);
    }

    /**
     * Generate an InvalidValueException from multiple error messages
     *
     * @param PreparationStepInterface $step
     * @param string $paramName
     * @param mixed $value
     * @param array|string[] $messages
     * @return InvalidValueException
     */
    public static function fromMessages(PreparationStepInterface $step, string $paramName, $value, array $messages)
    {
        Assert::allString($messages);

        $errors = array_map(function (string $message) use ($paramName) {
            return new ParameterError($message, $paramName);
        }, $messages);

        return new static($step, $value, $errors);
    }

    /**
     * PreparationStepException constructor.
     *
     * @param PreparationStepInterface $step
     * @param mixed $value
     * @param array|ParameterError[] $errors
     */
    public function __construct(PreparationStepInterface $step, $value, array $errors)
    {
        Assert::allIsInstanceOf($errors, ParameterError::class);

        $message = sprintf('Parameter preparation step failed (invalid data): ' . get_class($step));
        $message .= '; ' . implode(PHP_EOL, $errors);

        parent::__construct($message, 422);

        $this->step = $step;
        $this->value = $value;
        array_map([$this, 'addError'], $errors);
    }

    /**
     * Which step failed?
     *
     * @return PreparationStepInterface
     */
    public function getStep(): PreparationStepInterface
    {
        return $this->step;
    }

    /**
     * Get the raw parameter value that was invalid
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
