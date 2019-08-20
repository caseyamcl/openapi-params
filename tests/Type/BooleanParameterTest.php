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

namespace Paramee\Type;

use Paramee\AbstractParameterTest;
use Paramee\Model\Parameter;

/**
 * Class BooleanParameterTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class BooleanParameterTest extends AbstractParameterTest
{
    /**
     * @return array
     */
    protected function getTwoOrMoreValidValues(): array
    {
        return [true, false];
    }


    /**
     * Return values that are not the correct type, but can be automatically type-cast if that is enabled
     *
     * @return array|mixed[]  Values for type cast check
     */
    protected function getValuesForTypeCastTest(): array
    {
        return [1, '1', 0, '0'];
    }

    /**
     * @param string $name
     * @return Parameter
     */
    protected function getInstance(string $name = 'test'): Parameter
    {
        return new BooleanParameter($name);
    }
}
