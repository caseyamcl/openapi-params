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

declare(strict_types=1);

namespace OpenApiParams\Format;

use OpenApiParams\Model\AbstractParamFormatTestBase;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Type\StringParameter;
use RuntimeException;

class NonExistentFormatTest extends AbstractParamFormatTestBase
{
    public function testGetName()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not automatically derive format name from class');
        /* @phpstan-ignore-next-line (it's the point of this test) */
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
