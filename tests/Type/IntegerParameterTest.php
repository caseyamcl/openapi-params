<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/openapi-params
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\Type;

use OpenApiParams\Model\AbstractNumericParameterTest;
use OpenApiParams\Model\Parameter;

class IntegerParameterTest extends AbstractNumericParameterTest
{
    /**
     * @param int $value
     * @return double|float|int
     */
    protected function cast(int $value)
    {
        return (int) $value;
    }

    protected function getTwoOrMoreValidValues(): array
    {
        return [1, -45, 20452];
    }

    /**
     * Return values that are not the correct type, but can be automatically type-cast if that is enabled
     *
     * @return array|mixed[]  Values for type cast check
     */
    protected function getValuesForTypeCastTest(): array
    {
        return ['34.5', '29', 52.0, -194.3];
    }

    /**
     * @param string $name
     * @return IntegerParameter
     */
    protected function getInstance(string $name = 'test'): Parameter
    {
        return new IntegerParameter($name);
    }
}
