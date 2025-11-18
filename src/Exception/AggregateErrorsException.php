<?php

/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\Exception;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use OpenApiParams\Behavior\ParameterErrorsTrait;
use OpenApiParams\Contract\ParameterException;
use OpenApiParams\Model\ParameterError;
use RuntimeException;
use Throwable;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * Aggregate Parameter Exception - Represents multiple errors
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 * @implements IteratorAggregate<int,ParameterException>
 */
class AggregateErrorsException extends RuntimeException implements IteratorAggregate, Countable, ParameterException
{
    use ParameterErrorsTrait;

    /**
     * @var array<int,ParameterException>
     */
    private array $exceptions;

    /**
     * AggregateParameterErrorsException constructor.
     *
     * @param iterable<ParameterException> $exceptions
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(iterable $exceptions, int $code = 0, ?Throwable $previous = null)
    {
        $this->exceptions = is_array($exceptions) ? $exceptions : iterator_to_array($exceptions);
        Assert::allIsInstanceOf($this->exceptions, ParameterException::class);

        $message = count($exceptions) === 1
            ? 'There was 1 validation error: ' . current($this->exceptions)->getMessage() /** @phpstan-ignore-line */
            : sprintf("There were %s validation errors", number_format(count($exceptions)));

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get count of exceptions (not errors)
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->exceptions);
    }

    /**
     * Retrieve a new ArrayIterator of the exceptions
     *
     * @return ArrayIterator<int,ParameterException>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->exceptions);
    }

    /**
     * @return array<string,ParameterError>
     */
    public function getErrors(): array
    {
        $errors = [];
        foreach ($this->getIterator() as $exception) {
            $errors = array_merge($errors, $exception->getErrors());
        }
        return $errors;
    }
}
