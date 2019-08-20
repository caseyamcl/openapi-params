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

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Paramee\Contract\PreparationStepInterface;
use Paramee\PreparationStep\CallbackStep;
use Paramee\Type\StringParameter;

/**
 * OpenAPI Date Format
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DateFormat extends DateTimeFormat
{
    public const TYPE = StringParameter::class;
    public const NAME = 'date';

    protected const DATE_FORMAT = 'Y-m-d';
    protected const DATE_FORMAT_EXAMPLE = '2017-07-21';

    /**
     * Get built-in parameter preparation steps
     *
     * These run after validation but before any custom preparation steps
     *
     * @return array|PreparationStepInterface[]
     */
    public function getPreparationSteps(): array
    {
        return [
            new CallbackStep(function (string $value): DateTimeImmutable {
                return CarbonImmutable::createFromFormat(static::DATE_FORMAT, $value);
            }, 'build date from format')
        ];
    }

    /**
     * @return string|null
     */
    public function getDocumentation(): ?string
    {
        return 'Value must be a valid RDC 3339 (section 5.6) date; e.g. "2017-07-21".';
    }


}
