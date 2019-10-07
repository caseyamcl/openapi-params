<?php

/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\Exception;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Paramee\Behavior\ParameterErrorsTrait;
use Paramee\Contract\ParameterException;
use Paramee\Model\ParameterError;
use RuntimeException;
use Throwable;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * Aggregate Parameter Exception - Represents multiple errors
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class AggregateErrorsException extends RuntimeException implements IteratorAggregate, Countable, ParameterException
{
    use ParameterErrorsTrait;

    /**
     * @var array|ParameterException[]
     */
    private $exceptions;

    /**
     * AggregateParameterErrorsException constructor.
     *
     * @param Traversable|array|ParameterException[] $exceptions
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(iterable $exceptions, $code = 0, Throwable $previous = null)
    {
        $this->exceptions = is_array($exceptions) ? $exceptions : iterator_to_array($exceptions);
        Assert::allIsInstanceOf($this->exceptions, ParameterException::class);

        $message = count($exceptions) === 1
            ? 'There was 1 validation error: ' . current($this->exceptions)->getMessage()
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
     * @return ArrayIterator|ParameterException[]
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->exceptions);
    }

    /**
     * @return array|ParameterError[]
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
