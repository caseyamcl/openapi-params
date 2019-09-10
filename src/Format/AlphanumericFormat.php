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

namespace Paramee\Format;

use Respect\Validation\Validator;
use Paramee\Model\AbstractParamFormat;
use Paramee\Model\ParameterValidationRule;
use Paramee\Type\StringParameter;

/**
 * Class AlphanumericFormat
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class AlphanumericFormat extends AbstractParamFormat
{
    public const NAME = 'alphanumeric';
    public const TYPE_CLASS = StringParameter::class;

    /**
     * @var string
     */
    private $extraChars = '';

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
            'Value can contain only alphanumeric characters %s',
            $this->extraChars ? 'or any of the following: ' . $this->extraChars : ''
        )) . '.';
    }


    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array|ParameterValidationRule[]
     */
    public function getValidationRules(): array
    {
        return [
            new ParameterValidationRule(
                Validator::alnum($this->extraChars),
                trim(sprintf(
                    'value must be alphanumeric %s',
                    ($this->extraChars ? "(also allowed: {$this->extraChars})" : '')
                ))
            )
        ];
    }
}
