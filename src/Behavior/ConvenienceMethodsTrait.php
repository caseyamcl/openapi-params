<?php
/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 * @package caseyamcl/openapi-params
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\Behavior;

use OpenApiParams\Model\Parameter;
use OpenApiParams\Model\ParameterList;
use OpenApiParams\Type\ArrayParameter;
use OpenApiParams\Type\BooleanParameter;
use OpenApiParams\Type\IntegerParameter;
use OpenApiParams\Type\NumberParameter;
use OpenApiParams\Type\ObjectParameter;
use OpenApiParams\Type\StringParameter;
use OpenApiParams\Format;

/**
 * Trait ConvenienceMethodsTrait
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
trait ConvenienceMethodsTrait
{
    /**
     * Add string parameter
     *
     * @param string $name
     * @param bool $required
     * @return StringParameter
     */
    public function addString(string $name, bool $required = false): StringParameter
    {
        return $this->addValue(new StringParameter($name, $required));
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
        return $this->addValue(new ArrayParameter($name, $required));
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
        return $this->addValue(new BooleanParameter($name, $required));
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
        return $this->addValue(new IntegerParameter($name, $required));
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
        return $this->addValue(new NumberParameter($name, $required));
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
        return $this->addValue(new ObjectParameter($name, $required));
    }

    public function addAlphaNumeric(string $name, string $extraChars = '', bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\AlphanumericFormat($extraChars)));
    }

    public function addBinary(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\BinaryFormat()));
    }

    public function addByte(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\ByteFormat()));
    }

    public function addCsv(string $name, bool $required = false, string $separator = ','): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\CsvFormat($separator)));
    }

    public function addDate(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\DateFormat()));
    }

    public function addDateTime(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\DateTimeFormat()));
    }

    public function addPassword(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\PasswordFormat()));
    }

    public function addUuid(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\UuidFormat()));
    }

    public function addYesNo(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\YesNoFormat()));
    }

    public function addEmail(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\EmailFormat()));
    }

    /**
     * Add a value to the parameter list
     *
     * @param Parameter $param
     * @return Parameter
     */
    protected function addValue(Parameter $param): Parameter
    {
        return $this->getParameterList()->add($param);
    }

    /**
     * @return ParameterList
     */
    abstract protected function getParameterList(): ParameterList;
}