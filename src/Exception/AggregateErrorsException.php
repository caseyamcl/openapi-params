<?php
/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @package caseyamcl/paramee
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
use Throwable;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * Aggregate Parameter Exception
 *
 * @package Paramee\Exception
 */
class AggregateErrorsException extends ParameterException implements IteratorAggregate, Countable
{
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
     * Get count of exceptions
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
}