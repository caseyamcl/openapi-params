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

namespace OpenApiParams\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 * Parameter Value Collection for use during parameter processing
 *
 * Contains instances of ParameterValue, which in turn encapsulates the raw/prepared values for each value
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class ParameterValues implements Countable, IteratorAggregate
{
    private array $values = [];
    private ParameterValuesContext $context;

    /**
     * Create parameter values using single parameter value
     *
     * @param mixed $value
     * @param ParameterValuesContext|null $context
     * @param string $name
     * @return ParameterValues
     */
    public static function single(
        mixed $value,
        ?ParameterValuesContext $context = null,
        string $name = '(no name)'
    ): ParameterValues {
        return new self([$name => $value], $context);
    }

    /**
     * ParameterValues constructor.
     */
    public function __construct(iterable $values, ?ParameterValuesContext $context = null)
    {
        foreach ($values as $name => $value) {
            $this->values[(string) $name] = ($value instanceof ParameterValue)
                ? $value
                : new ParameterValue((string) $name, $value);
        }

        $this->context = $context ?: new ParameterValuesContext();
    }

    /**
     * @return ParameterValuesContext
     */
    public function getContext(): ParameterValuesContext
    {
        return $this->context;
    }

    /**
     * Returns TRUE if the value was passed, even if the value itself is NULL
     */
    public function hasValue(string $name): bool
    {
        return isset($this->values[$name]);
    }

    public function getPreparedValue(string $name): mixed
    {
        if ($this->hasValue($name)) {
            return $this->values[$name]->getPreparedValue();
        } else {
            throw new RuntimeException('Parameter not found: ' . $name);
        }
    }

    public function getRawValue(string $name): mixed
    {
        if ($this->hasValue($name)) {
            return $this->values[$name]->getRawValue();
        } else {
            throw new RuntimeException('Parameter not found: ' . $name);
        }
    }

    /**
     * Get the ParameterValue item for a parameter
     */
    public function get(string $name): ParameterValue
    {
        if ($this->hasValue($name)) {
            return $this->values[$name];
        } else {
            throw new RuntimeException('Parameter not found: ' . $name);
        }
    }

    /**
     * @return ArrayIterator<int,ParameterValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->values);
    }

    /**
     * List parameter names as array
     *
     * @return array<int,string>
     */
    public function listNames(): array
    {
        return array_keys($this->values);
    }

    public function count(): int
    {
        return count($this->values);
    }

    /**
     * Get a copy of this object with added raw value
     */
    public function withRawValue(string $name, mixed $rawValue): ParameterValues
    {
        if ($name === '') {
            throw new RuntimeException('Cannot add a raw value for parameter with no name');
        } elseif (array_key_exists($name, $this->values)) {
            throw new RuntimeException('Cannot add a raw value for already existing parameter: ' . $name);
        }

        $that = clone $this;
        $that->values[$name] = new ParameterValue($name, $rawValue);
        return $that;
    }

    /**
     * Get a copy of this object with value set to prepared
     *
     * NOTE: This method can only be run once per parameter
     */
    public function withPreparedValue(string $name, mixed $value): ParameterValues
    {
        if ($name === '') {
            throw new RuntimeException('Cannot set prepared value for parameter with no name');
        } elseif (! $this->hasValue($name)) {
            throw new RuntimeException('Cannot set prepared value for undefined parameter: ' . $name);
        }

        $that = clone $this;
        $that->values[$name] = $that->values[$name]->withPreparedValue($value);
        return $that;
    }
}
