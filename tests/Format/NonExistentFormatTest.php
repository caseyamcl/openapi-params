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

declare(strict_types=1);

namespace Format;

use Paramee\Model\AbstractParamFormatTest;
use Paramee\Contract\ParamFormat;
use Paramee\Model\AbstractParamFormat;
use Paramee\Model\Parameter;
use Paramee\Type\StringParameter;
use RuntimeException;

class NonExistentFormatTest extends AbstractParamFormatTest
{
    public function testGetName()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not automatically derive format name from class');
        $this->getFormat()->getName();
    }

    public function testToStringThrowsExceptionOnInabilityToDetermineFormatName()
    {
        $this->assertEmpty($this->getParameterWithFormat()->getFormat()->__toString());
    }

    /**
     * Returns anonymous class that extends AbstractParamFormat, but does not explicitly include a name
     *
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
    {
        return new class extends AbstractParamFormat {
            public const TYPE_CLASS = StringParameter::class;
            public function getValidationRules(): array
            {
                return [];
            }
            public function __toString(): string
            {
                return parent::__toString();
            }
        };
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter('test'))->setFormat($this->getFormat());
    }
}
