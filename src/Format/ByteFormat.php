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

use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\PreparationStep\CallbackStep;
use OpenApiParams\Type\StringParameter;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * OpenAPI String Byte Format
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ByteFormat extends AbstractParamFormat
{
    public const TYPE_CLASS = StringParameter::class;
    public const NAME = 'byte';

    private const BASE64_REGEX = '^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$';

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
                new Regex(self::BASE64_REGEX),
                'value must be base64-encoded'
            )
        ];
    }

    /**
     * Get built-in parameter preparation steps
     *
     * These run after validation but before any custom preparation steps
     *
     * @return array<int,PreparationStep>
     */
    public function getPreparationSteps(): array
    {
        return [new CallbackStep(function (string $value): string {
            return base64_decode($value);
        }, 'base64 decode the value')];
    }

    public function getDocumentation(): ?string
    {
        return "Value must a base64-encoded string.";
    }
}
