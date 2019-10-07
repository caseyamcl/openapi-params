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

namespace Paramee;

use RuntimeException;
use Paramee\Model\Parameter;
use Paramee\Type\ArrayParameter;
use Paramee\Type\BooleanParameter;
use Paramee\Type\IntegerParameter;
use Paramee\Type\NumberParameter;
use Paramee\Type\ObjectParameter;
use Paramee\Type\StringParameter;
use Webmozart\Assert\Assert;

/**
 * Class ParamTypes
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class ParamTypes
{
    public const STRING = 'string';
    public const BOOLEAN = 'boolean';
    public const OBJECT = 'object';
    public const ARRAY = 'array';
    public const NUMBER = 'number';
    public const INTEGER = 'integer';

    /**
     * Create a type instance of a given parameter
     *
     * @param string $type        Parameter type (OpenApi) name
     * @param string $paramName   Optional name
     * @return Parameter
     */
    public static function resolveTypeInstance(string $type, $paramName = ''): Parameter
    {
        $map = [
            self::STRING  => StringParameter::class,
            self::INTEGER => IntegerParameter::class,
            self::ARRAY   => ArrayParameter::class,
            self::OBJECT  => ObjectParameter::class,
            self::NUMBER  => NumberParameter::class,
            self::BOOLEAN => BooleanParameter::class
        ];

        Assert::oneOf($type, array_keys($map));

        $className = $map[$type];
        return new $className($paramName);
    }

    /**
     * Attempt to resolve a parameter type for a given PHP value
     *
     * @param mixed $value
     * @param string $name
     * @return Parameter
     */
    public static function resolveParameterForValue($value, string $name = ''): Parameter
    {
        switch (true) {
            case is_object($value):
                return self::resolveTypeInstance(self::OBJECT, $name);
            case is_array($value):
                return self::resolveTypeInstance(self::ARRAY, $name);
            case is_integer($value):
                return self::resolveTypeInstance(self::INTEGER, $name);
            case is_numeric($value):
                return self::resolveTypeInstance(self::NUMBER, $name);
            case is_bool($value):
                return self::resolveTypeInstance(self::BOOLEAN, $name);
            case is_string($value):
                return self::resolveTypeInstance(self::STRING, $name);
            default:
                $type = gettype($value);
                throw new RuntimeException("Cannot resolve parameter for item of type $type");
        }
    }
}
