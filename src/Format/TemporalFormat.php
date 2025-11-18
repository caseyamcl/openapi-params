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

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use OpenApiParams\Contract\ParamValidationRule;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\PreparationStep\CallbackStep;
use OpenApiParams\Type\StringParameter;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * Temporal format (any valid date string)
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class TemporalFormat extends DateTimeFormat
{
    public const string NAME = 'temporal';
    public const string TYPE_CLASS = StringParameter::class;

    public function getPreValidationPreparationSteps(): array
    {
        return [
            new CallbackStep(function (string $value): DateTimeImmutable {
                return CarbonImmutable::parse($value);
            }, 'parse date string to CarbonImmutable instance')
        ];
    }

    public function getDocumentation(): ?string
    {
        return 'value must be a valid date/time (examples: "now", "2017-03-04", "tomorrow", "2017-03-04T01:30:40Z").';
    }

    protected function getBaseRule(): ParamValidationRule
    {
        return new ParameterValidationRule(
            new Callback(function (string $value) {
                try {
                    return (bool) CarbonImmutable::parse($value);
                } catch (Exception | InvalidArgumentException) {
                    return false;
                }
            }),
            sprintf('value must be valid RFC3339 date/time (example: %s)', static::DATE_FORMAT_EXAMPLE)
        );
    }
}
