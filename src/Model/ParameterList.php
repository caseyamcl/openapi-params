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

use ArrayObject;
use Countable;
use Generator;
use IteratorAggregate;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;
use MJS\TopSort\Implementations\StringSort;
use OpenApiParams\Behavior\ConvenienceMethodsTrait;
use OpenApiParams\Exception\AggregateErrorsException;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Exception\MissingParameterException;
use OpenApiParams\Exception\UndefinedParametersException;
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

    private string $name;
    private ?ParameterValuesContext $context;

    /**
     * @var ArrayObject<string,Parameter>  key is parameter name
     */
    private ArrayObject $parameters;

    /**
     * ParameterList constructor.
     *
     * @param string $name
     * @param iterable<int,Parameter> $items
     * @param ParameterValuesContext|null $context
     */
    public function __construct(string $name, iterable $items = [], ?ParameterValuesContext $context = null)
    {
        Assert::allIsInstanceOf($items, Parameter::class);

        $this->name = $name;
        $this->parameters = new ArrayObject();

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
        $this->parameters[$param->getName()] = $param;
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
            $diff = array_diff($paramValues->listNames(), array_keys($this->parameters->getArrayCopy()));
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

            // ...or skip parameters that are optional and missing from the values
            if ($paramValues->hasValue($param->getName())) {
                $paramValue = $paramValues->get($param->getName())->getRawValue();
            } elseif ($param->hasDefault()) {
                $paramValue = $param->getDefault();
                $paramValues = $paramValues->withRawValue($param->getName(), $param->getDefault());
            } else {
                continue;
            }

            try {
                $param->prepare($paramValue, $paramValues);
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

    public function getParameters(): ArrayObject
    {
        return $this->parameters;
    }

    public function getContext(): ?ParameterValuesContext
    {
        return $this->context;
    }

    /**
     * Get the OpenAPI documentation for this set of parameters
     */
    public function getApiDocumentation(): array
    {
        $apiDocs = [];
        foreach ($this->parameters as $name => $parameter) {
            $apiDocs[$name] = $parameter->getDocumentation();
        }
        return $apiDocs;
    }

    public function get(string $name): Parameter
    {
        if ($this->has($name)) {
            return $this->parameters[$name];
        } else {
            throw new RuntimeException("Parameter not found: " . $name);
        }
    }

    /**
     * Check if a parameter is set
     */
    public function has(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    // --------------------------------------------------------------
    // Methods to implement interfaces

    public function count(): int
    {
        return $this->parameters->count();
    }

    public function getIterator(): Generator
    {
        foreach ($this->parameters as $name => $value) {
            yield $name => $value;
        }
    }

    /**
     * @return iterable<int,Parameter>
     * @throws CircularDependencyException
     * @throws ElementNotFoundException
     */
    private function getOrderedParams(): iterable
    {
        $sorter = new StringSort();

        foreach ($this->parameters as $parameter) {
            $paramDependencies = array_unique(array_merge(
                $parameter->listDependencies(),
                $parameter->listOptionalDependencies($this->listParameterNames())
            ));
            $sorter->add($parameter->getName(), $paramDependencies);
        }

        foreach ($sorter->sort() as $name) {
            yield $this->get($name);
        }
    }

    /**
     * @return array<int,string>
     */
    protected function listParameterNames(): array
    {
        return array_keys($this->parameters->getArrayCopy());
    }

    protected function getParameterList(): ParameterList
    {
        return $this;
    }
}
