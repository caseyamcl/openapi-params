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

namespace Paramee\Type;

use Respect\Validation\Validator;
use Paramee\Contract\ParamFormatInterface;
use Paramee\Model\AbstractNumericParameter;
use Paramee\Model\ParameterValidationRule;
use Paramee\Format\Int32Format;
use Paramee\Format\Int64Format;

/**
 * Class IntegerParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class IntegerParameter extends AbstractNumericParameter
{
    public const TYPE_NAME = 'integer';
    public const PHP_DATA_TYPE = 'integer';

    public function __construct(string $name = '', bool $required = false)
    {
        parent::__construct($name, $required);
        $this->format = $this->buildFormat(); // integer types will always have a format
    }

    /**
     * @return ParamFormatInterface|null
     */
    protected function buildFormat(): ?ParamFormatInterface
    {
        return PHP_INT_SIZE === 8 ? new Int64Format() : new Int32Format();
    }

    protected function getBuiltInValidationRules(): array
    {
        return array_merge(
            parent::getBuiltInValidationRules(),
            [new ParameterValidationRule(Validator::intType())]
        );
    }
}
