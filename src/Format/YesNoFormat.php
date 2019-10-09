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

namespace Paramee\Format;

use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Rules\Callback;
use Paramee\Contract\PreparationStep;
use Paramee\Model\AbstractParamFormat;
use Paramee\Model\ParameterValidationRule;
use Paramee\PreparationStep\CallbackStep;
use Paramee\Type\StringParameter;

/**
 * Custom Yes/No (true/false, 1/0, on/off) string format
 *
 * This converts the string to a boolean
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class YesNoFormat extends AbstractParamFormat
{
    public const TYPE_CLASS = StringParameter::class;
    public const NAME = 'yesno';

    public const BOOLEAN_MAP = [
        'true'  => true,  '1' => true,  'yes' => true, 'on' => true,
        'false' => false, '0' => false, 'no' => false, 'off' => false
    ];

    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array|ParameterValidationRule[]
     * @throws ComponentException
     */
    public function getValidationRules(): array
    {
        return [
            new ParameterValidationRule(
                // Tried to use the Respect 'in' rule here, but it automatically typecast integer values, so I had
                // to use a callback function instead.
                new Callback(function ($value) {
                    return isset(static::BOOLEAN_MAP[(string) $value]);
                }),
                sprintf('must be one of: %s', implode(', ', array_keys(static::BOOLEAN_MAP)))
            )
        ];
    }

    /**
     * Get built-in parameter preparation steps
     *
     * These run after validation but before any custom preparation steps
     *
     * @return array|PreparationStep[]
     */
    public function getPreparationSteps(): array
    {
        return [
            new CallbackStep(function (string $value): bool {
                return static::BOOLEAN_MAP[$value];
            }, 'convert yes/no value to boolean')
        ];
    }

    public function getDocumentation(): ?string
    {
        return 'Value must be "true/false", "yes/no", "on/off", or "1/0".';
    }
}
