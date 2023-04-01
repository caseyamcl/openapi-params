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
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Respect\Validation\Validator;
use OpenApiParams\Contract\ParamValidationRule;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\PreparationStep\CallbackStep;
use OpenApiParams\Type\StringParameter;

/**
 * OpenAPI DateTime Format
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DateTimeFormat extends AbstractParamFormat
{
    public const TYPE_CLASS = StringParameter::class;
    public const NAME = 'date-time';

    public const VALID_FORMATS = [DateTime::RFC3339, DateTime::RFC3339_EXTENDED];
    public const DATE_FORMAT_EXAMPLE = '2017-07-21T17:32:28Z';

    private ?CarbonImmutable $earliestDate;

    private ?CarbonImmutable $latestDate;

    /**
     * DateTimeFormat constructor.
     *
     * @param DateTimeInterface|null $earliest  Specify the oldest allowable date/time (inclusive)
     * @param DateTimeInterface|null $latest  Specify the newest allowable date/time (inclusive)
     */
    public function __construct(?DateTimeInterface $earliest = null, DateTimeInterface $latest = null)
    {
        $this->setEarliestDate($earliest);
        $this->setLatestDate($latest);
    }

    /**
     * @param DateTimeInterface|null $earliestDate
     */
    public function setEarliestDate(?DateTimeInterface $earliestDate): void
    {
        $this->earliestDate = $earliestDate ? CarbonImmutable::instance($earliestDate) : null;
        ;
    }

    /**
     * @param DateTimeInterface|null $latestDate
     */
    public function setLatestDate(?DateTimeInterface $latestDate): void
    {
        $this->latestDate = $latestDate ? CarbonImmutable::instance($latestDate) : null;
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
        $rules[] = $this->getBaseRule();

        if ($this->earliestDate) {
            $rules[] = new ParameterValidationRule(
                Validator::min($this->earliestDate),
                sprintf(
                    'value must be newer than (inclusive) %s',
                    $this->earliestDate->format(current(static::VALID_FORMATS))
                )
            );
        }

        if ($this->latestDate) {
            $rules[] = new ParameterValidationRule(
                Validator::max($this->latestDate),
                sprintf(
                    'value must be older than (inclusive) %s',
                    $this->latestDate->format(current(static::VALID_FORMATS))
                )
            );
        }

        return $rules;
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
        return [
            new CallbackStep(function (string $value): DateTimeImmutable {
                return $this->buildDate($value);
            }, 'build date/time from format')
        ];
    }

    public function getDocumentation(): ?string
    {
        return 'Value must be a valid RFC 3339 (section 5.6) date-time; e.g. "' . static::DATE_FORMAT_EXAMPLE . '".';
    }

    protected function getBaseRule(): ParamValidationRule
    {
        return new ParameterValidationRule(
            Validator::callback(function (string $value) {
                try {
                    return (bool) $this->buildDate($value);
                } catch (InvalidArgumentException $e) {
                    return false;
                }
            }),
            sprintf('value must be valid RFC3339 date/time (example: %s)', static::DATE_FORMAT_EXAMPLE)
        );
    }

    /**
     * Build a Carbon object from a date string or throw exception
     *
     * @param string $value
     * @return CarbonImmutable|null
     * @throws Exception
     */
    public function buildDate(string $value): ?CarbonImmutable
    {
        $lastException = null;
        foreach (static::VALID_FORMATS as $format) {
            try {
                return CarbonImmutable::createFromFormat($format, $value);
            } catch (Exception $e) {
                $lastException = $e;
            }
        }

        throw $lastException;
    }
}
