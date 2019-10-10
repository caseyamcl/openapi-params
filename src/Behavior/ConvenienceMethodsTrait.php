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

namespace Paramee\Behavior;

use Paramee\Model\Parameter;
use Paramee\Model\ParameterList;
use Paramee\Type\ArrayParameter;
use Paramee\Type\BooleanParameter;
use Paramee\Type\IntegerParameter;
use Paramee\Type\NumberParameter;
use Paramee\Type\ObjectParameter;
use Paramee\Type\StringParameter;
use Paramee\Format;

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
    public function addStringValue(string $name, bool $required = false): StringParameter
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
    public function addArrayValue(string $name, bool $required = false): ArrayParameter
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
    public function addBooleanValue(string $name, bool $required = false): BooleanParameter
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
    public function addObjectValue(string $name, bool $required = false): ObjectParameter
    {
        return $this->addValue(new ObjectParameter($name, $required));
    }

    public function addAlphaNumericValue(string $name, bool $required = false, string $extraChars = ''): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\AlphanumericFormat($extraChars)));
    }

    public function addBinaryValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\BinaryFormat()));
    }

    public function addByteValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\ByteFormat()));
    }

    public function addCsvValue(string $name, bool $required = false, string $separator = ','): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\CsvFormat($separator)));
    }

    public function addDateValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\DateFormat()));
    }

    public function addDateTimeValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\DateTimeFormat()));
    }

    public function addPasswordValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\PasswordFormat()));
    }

    public function addUuidValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\UuidFormat()));
    }

    public function addYesNoValue(string $name, bool $required = false): StringParameter
    {
        $param = new StringParameter($name, $required);
        return $this->addValue($param->setFormat(new Format\YesNoFormat()));
    }

    public function addEmailValue(string $name, bool $required = false): StringParameter
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