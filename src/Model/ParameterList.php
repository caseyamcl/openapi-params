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

namespace Paramee\Model;

use ArrayObject;
use Countable;
use Generator;
use IteratorAggregate;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;
use MJS\TopSort\Implementations\StringSort;
use Paramee\Behavior\ConvenienceMethodsTrait;
use Paramee\Exception\AggregateErrorsException;
use Paramee\Exception\InvalidValueException;
use Paramee\Exception\MissingParameterException;
use Paramee\Exception\UndefinedParametersException;
use RuntimeException;
use Webmozart\Assert\Assert;

/**
 * Parameter List contains a mutable list of parameters
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ParameterList implements IteratorAggregate, Countable
{
    use ConvenienceMethodsTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var ArrayObject|Parameter[]
     */
    private $items;

    /**
     * @var ParameterValuesContext|null
     */
    private $context;

    /**
     * ParameterList constructor.
     *
     * @param string $name
     * @param iterable|Parameter[] $items
     * @param ParameterValuesContext|null $context
     */
    public function __construct(string $name, iterable $items = [], ?ParameterValuesContext $context = null)
    {
        Assert::allIsInstanceOf($items, Parameter::class);

        $this->name = $name;
        $this->items = new ArrayObject();

        foreach ($items as $item) {
            $this->add($item);
        }

        $this->context = $context;
    }

    /**
     * Add a parameter
     *
     * @param Parameter $param
     * @return Parameter  The added parameter
     */
    public function add(Parameter $param): Parameter
    {
        $this->items[$param->getName()] = $param;
        return $param;
    }

    // --------------------------------------------------------------
    // Preparation

    /**
     * Prepare some values
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @param iterable $values
     * @param bool $strict If TRUE, then undefined parameters will create an error, otherwise they will be ignored
     * @return ParameterValues
     */
    public function prepare(iterable $values, bool $strict = true): ParameterValues
    {
        $paramValues = ($values instanceof ParameterValues)
            ? $values
            : new ParameterValues($values, $this->getContext());

        // Check for undefined parameters
        if ($strict) {
            $diff = array_diff($paramValues->listNames(), array_keys($this->items->getArrayCopy()));
            if (! empty($diff)) {
                $exceptions[] = new UndefinedParametersException($diff);
            }
        }

        // Iterate through items and prepare each of them.
        /** @noinspection PhpUnhandledExceptionInspection */
        foreach ($this->getOrderedParams() as $param) {
            // Check if parameter is required, and throw exception if it is not in the values
            if ($param->isRequired() && ! $paramValues->hasValue($param->getName())) {
                $exceptions[] = new MissingParameterException($param->getName());
            }

            // ..or skip parameters that are optional and missing from the values
            if (! $paramValues->hasValue($param->getName())) {
                continue;
            }

            try {
                $param->prepare($paramValues->get($param->getName())->getRawValue(), $paramValues);
            } catch (InvalidValueException | MissingParameterException $e) {
                $exceptions[] = $e;
            }
        }

        if (isset($exceptions)) {
            throw new AggregateErrorsException($exceptions);
        }

        return $paramValues;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ArrayObject
     */
    public function getParameters(): ArrayObject
    {
        return $this->items;
    }

    /**
     * @return ParameterValuesContext|null
     */
    public function getContext(): ?ParameterValuesContext
    {
        return $this->context;
    }

    /**
     * Get the OpenAPI documentation for this set of parameters
     *
     * @return array
     */
    public function getApiDocumentation(): array
    {
        $apiDocs = [];
        foreach ($this->items as $name => $parameter) {
            $apiDocs[$name] = $parameter->getDocumentation();
        }
        return $apiDocs;
    }

    /**
     * @param string $name
     * @return Parameter
     */
    public function get(string $name): Parameter
    {
        if ($this->has($name)) {
            return $this->items[$name];
        } else {
            throw new RuntimeException("Parameter not found: " . $name);
        }
    }

    /**
     * Check if a parameter is set
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->items[$name]);
    }

    // --------------------------------------------------------------
    // Methods to implement interfaces

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->items->count();
    }

    public function getIterator(): Generator
    {
        foreach ($this->items as $name => $value) {
            yield $name => $value;
        }
    }

    /**
     *
     * @return iterable|Parameter[]
     * @throws CircularDependencyException
     * @throws ElementNotFoundException
     */
    private function getOrderedParams(): iterable
    {
        $sorter = new StringSort();

        foreach ($this->items as $parameter) {
            $sorter->add($parameter->getName(), $parameter->listDependencies());
        }

        foreach ($sorter->sort() as $name) {
            yield $this->get($name);
        }
    }

    /**
     * @return ParameterList
     */
    protected function getParameterList(): ParameterList
    {
        return $this;
    }
}
