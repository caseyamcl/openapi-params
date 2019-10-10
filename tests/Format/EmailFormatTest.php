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

namespace Paramee\Format;

use Paramee\Contract\ParamFormat;
use Paramee\Exception\InvalidValueException;
use Paramee\Model\AbstractParamFormatTest;
use Paramee\Model\Parameter;
use Paramee\Type\StringParameter;

class EmailFormatTest extends AbstractParamFormatTest
{
    public function testValidValueIsPrepared()
    {
        $prepared = $this->getParameterWithFormat()->prepare('test@example.org');
        $this->assertSame('test@example.org', $prepared);
    }

    public function testInvalidValueThrowsException()
    {
        // local part of email cannot contain two dots (..)
        $this->expectException(InvalidValueException::class);
        $this->getParameterWithFormat()->prepare('test..abc@example.org');
    }

    /**
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
    {
        return new EmailFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter())->setFormat($this->getFormat());
    }
}
