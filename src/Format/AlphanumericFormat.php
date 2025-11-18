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

namespace OpenApiParams\Format;

use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Type\StringParameter;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Class AlphanumericFormat
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class AlphanumericFormat extends AbstractParamFormat
{
    public const string NAME = 'alphanumeric';
    public const string TYPE_CLASS = StringParameter::class;

    private const ALPHANUMERIC_PATTERN = "a-zA-Z0-9";

    private string $extraChars;

    /**
     * AlphanumericFormat constructor.
     * @param string $extraChars
     */
    public function __construct(string $extraChars = '')
    {
        $this->extraChars = $extraChars;
    }

    /**
     * Sets (clobbers) allowed extra characters in the string
     *
     * @param string $chars
     * @return self
     */
    public function setExtraChars(string $chars): self
    {
        $this->extraChars = $chars;
        return $this;
    }

    public function getDocumentation(): ?string
    {
        return trim(sprintf(
            'value can contain only alphanumeric characters %s',
            $this->extraChars ? 'or any of the following: ' . $this->extraChars : ''
        ));
    }


    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array<int,ParameterValidationRule>
     */
    public function getValidationRules(): array
    {
        $regexPattern = sprintf(
            '/^[%s%s]*$/',
            self::ALPHANUMERIC_PATTERN,
            $this->extraChars
        );

        return [
            new ParameterValidationRule(
                new Regex($regexPattern),
                trim(sprintf(
                    'value must be alphanumeric %s',
                    ($this->extraChars ? "(also allowed: $this->extraChars)" : '')
                ))
            )
        ];
    }
}
