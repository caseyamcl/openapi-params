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
use OpenApiParams\PreparationStep\CallbackStep;
use OpenApiParams\Type\StringParameter;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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

    public function getValidationRules(): array
    {
        return [
            new ParameterValidationRule(
                new Choice(array_map('strval', array_keys(static::BOOLEAN_MAP))),
                sprintf('must be one of: %s', implode(', ', array_keys(static::BOOLEAN_MAP)))
            )
        ];
    }

    public function getPostValidationPreparationSteps(): array
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
