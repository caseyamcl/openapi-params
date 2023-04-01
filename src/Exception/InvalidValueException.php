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

namespace OpenApiParams\Exception;

use OpenApiParams\Behavior\ParameterErrorsTrait;
use OpenApiParams\Contract\ParameterException;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Model\ParameterError;
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
     * Generate an InvalidValueException from a single error message
     */
    public static function fromMessage(PreparationStep $step, string $paramName, mixed $value, string $message): self
    {
        return InvalidValueException::fromMessages($step, $paramName, $value, [$message]);
    }

    /**
     * Generate an InvalidValueException from multiple error messages
     *
     * @param PreparationStep $step
     * @param string $paramName
     * @param mixed $value
     * @param array<int,string> $messages
     * @return InvalidValueException
     */
    public static function fromMessages(PreparationStep $step, string $paramName, mixed $value, array $messages): self
    {
        Assert::allString($messages);

        $errors = array_map(function (string $message) use ($paramName) {
            return new ParameterError($message, $paramName);
        }, $messages);

        return new InvalidValueException($step, $value, $errors);
    }

    /**
     * PreparationStepException constructor.
     *
     * @param PreparationStep $step
     * @param mixed $value
     * @param array|ParameterError[] $errors
     */
    public function __construct(
        private readonly PreparationStep $step,
        private readonly mixed $value,
        array $errors
    ) {
        Assert::allIsInstanceOf($errors, ParameterError::class);

        $message = 'Parameter preparation step failed (invalid data): ' . get_class($step);
        $message .= '; ' . implode(PHP_EOL, $errors);

        parent::__construct($message, 422);
        array_map([$this, 'addError'], $errors);
    }

    /**
     * Which step failed?
     */
    public function getStep(): PreparationStep
    {
        return $this->step;
    }

    /**
     * Get the raw parameter value that was invalid
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
