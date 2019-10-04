<?php
/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/paramee
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
use Paramee\Exception\AggregateErrorsException;
use Paramee\Exception\InvalidValueException;
use Paramee\Exception\MissingParameterException;
use Paramee\Exception\UndefinedParametersException;
use Paramee\Format;
use Paramee\Type\ArrayParameter;
use Paramee\Type\BooleanParameter;
use Paramee\Type\IntegerParameter;
use Paramee\Type\NumberParameter;
use Paramee\Type\ObjectParameter;
use Paramee\Type\StringParameter;

/**
 * Parameter List contains a mutable list of parameters
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ParameterList implements IteratorAggregate, Countable
{
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
        $this->items[$param->__toString()] = $param;
        return $param;
    }

    /**
     * Add string parameter
     *
     * @param string $name
     * @param bool $required
     * @return StringParameter
     */
    public function addString(string $name, bool $required = false): StringParameter
    {
        return $this->add(new StringParameter($name, $required));
    }

    /**
     * Add an array parameter
     *
     * @param string $name
     * @param bool $required
     * @return ArrayParameter
     */
    public function addArray(string $name, bool $required = false): ArrayParameter
    {
        return $this->add(new ArrayParameter($name, $required));
    }

    /**
     * Add a boolean parameter (strict boolean; use ParameterList->addYesNo() to add 'truthy' parameter)
     *
     * @param string $name
     * @param bool $required
     * @return BooleanParameter
     */
    public function addBoolean(string $name, bool $required = false): BooleanParameter
    {
        return $this->add(new BooleanParameter($name, $required));
    }

    /**
     * Add an integer parameter (strict integer; use ParameterList->addNumber() to add a more flexible number)
     *
     * @param string $name
     * @param bool $required
     * @return IntegerParameter
     */
    public function addInteger(string $name, bool $required = false): IntegerParameter
    {
        return $this->add(new IntegerParameter($name, $required));
    }

    /**
     * Add a number parameter (allows decimals; use ParameterList->addInteger() to add a strict integer)
     *
     * @param string $name
     * @param bool $required
     * @return NumberParameter
     */
    public function addNumber(string $name, bool $required = false): NumberParameter
    {
        return $this->add(new NumberParameter($name, $required));
    }

    /**
     * Add an object parameter
     *
     * @param string $name
     * @param bool $required
     * @return ObjectParameter
     */
    public function addObject(string $name, bool $required = false): ObjectParameter
    {
        return $this->add(new ObjectParameter($name, $required));
    }

    public function addAlphaNumericValue(string $name, bool $required = false, string $extraChars = ''): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->add($param->setFormat(new Format\AlphanumericFormat($extraChars)));
    }

    public function addBinaryValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->add($param->setFormat(new Format\BinaryFormat()));
    }

    public function addByteValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->add($param->setFormat(new Format\ByteFormat()));
    }

    public function addCsvValue(string $name, bool $required = false, string $separator = ','): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->add($param->setFormat(new Format\CsvFormat($separator)));
    }

    public function addDateValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->add($param->setFormat(new Format\DateFormat()));
    }

    public function addDateTimeValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->add($param->setFormat(new Format\DateTimeFormat()));
    }

    public function addPasswordValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->add($param->setFormat(new Format\PasswordFormat()));
    }

    public function addUuidValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->add($param->setFormat(new Format\UuidFormat()));
    }

    public function addYesNoValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->add($param->setFormat(new Format\YesNoFormat()));
    }

    // --------------------------------------------------------------
    // Preparation

    /**
     * Prepare some values
     *
     * @param iterable $values
     * @param bool $strict  If TRUE, then undefined parameters will create an error, otherwise they will be ignored
     * @return ParameterValues
     * @throws AggregateErrorsException  If there were 1 or more errors during preparation, this will throw an exception
     */
    public function prepare(iterable $values, bool $strict = true): ParameterValues
    {
        $paramValues = new ParameterValues($values, $this->getContext());

        // Check for undefined parameters
        if ($strict) {
            $diff = array_diff($paramValues->listNames(), array_keys($this->items->getArrayCopy()));
            if (! empty($diff)) {
                $errors[] = new UndefinedParametersException($diff);
            }
        }

        // Iterate through items and prepare each of them.
        foreach ($this->items as $param) {
            // Check if parameter is required, and throw exception if it is not in the values
            if ($param->isRequired() && ! $paramValues->hasValue($param->__toString())) {
                $errors[] = new MissingParameterException($param->__toString());
            }

            // ..or skip parameters that are optional and missing from the values
            if (! $paramValues->hasValue($param->__toString())) {
                continue;
            }

            try {
                $param->prepare($paramValues->get($param->__toString())->getRawValue(), $paramValues);
            } catch (InvalidValueException $e) {
                $errors[] = $e;
            }
        }

        if (isset($errors)) {
            throw new AggregateErrorsException($errors);
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
}
