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
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\PreparationStep\CallbackStep;
use OpenApiParams\Type\StringParameter;

/**
 * OpenAPI Date Format
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DateFormat extends DateTimeFormat
{
    public const TYPE_CLASS = StringParameter::class;
    public const NAME = 'date';

    public const VALID_FORMATS = ['Y-m-d'];
    public const DATE_FORMAT_EXAMPLE = '2017-07-21';

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
            new CallbackStep(function (string $value): DateTimeImmutable {
                return $this->buildDate($value);
            }, 'build date from format')
        ];
    }

    /**
     * Since this granularity level only concerns dates, we set all times to the beginning of the day
     *
     * @param string $value
     * @return CarbonImmutable|null
     * @throws Exception
     */
    public function buildDate(string $value): ?CarbonImmutable
    {
        return parent::buildDate($value)->startOfDay();
    }
}
