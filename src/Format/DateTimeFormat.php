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
use DateTimeInterface;
use InvalidArgumentException;
use Respect\Validation\Validator;
use Paramee\Contract\ParameterValidationRuleInterface;
use Paramee\Contract\PreparationStepInterface;
use Paramee\Model\AbstractParamFormat;
use Paramee\Model\ParameterValidationRule;
use Paramee\PreparationStep\CallbackStep;
use Paramee\Type\StringParameter;

/**
 * OpenAPI DateTime Format
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DateTimeFormat extends AbstractParamFormat
{
    public const TYPE_CLASS = StringParameter::class;
    public const NAME = 'date-time';

    protected const DATE_FORMAT = 'Y-m-dTH:i:sZ';
    protected const DATE_FORMAT_EXAMPLE = '2017-07-21T17:32:28Z';

    /**
     * @var DateTimeInterface|null
     */
    private $oldest;

    /**
     * @var DateTimeInterface|null
     */
    private $newest;

    /**
     * DateTimeFormat constructor.
     * @param DateTimeInterface|null $oldest  Specify oldest allowable date/time (inclusive)
     * @param DateTimeInterface|null $newest  Specify newest allowable date/time (inclusive)
     */
    public function __construct(DateTimeInterface $oldest = null, DateTimeInterface $newest = null)
    {
        $this->oldest = $oldest;
        $this->newest = $newest;
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
        $rules[] = $this->getBaseRule();

        if ($this->oldest) {
            $rules[] = new ParameterValidationRule(
                Validator::min($this->oldest->format('Y-m-dTH:i:sZ')),
                sprintf('value must be newer than (inclusive) %s', $this->oldest->format('Y-m-d H:i:s'))
            );
        }

        if ($this->newest) {
            $rules[] = new ParameterValidationRule(
                Validator::min($this->newest->format('Y-m-dTH:i:sZ')),
                sprintf('value must be older than (inclusive) %s', $this->newest->format('Y-m-d H:i:s'))
            );
        }

        return $rules;
    }

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
            }, 'build date/time from format')
        ];
    }

    /**
     * @return string|null
     */
    public function getDocumentation(): ?string
    {
        return 'Value must be a valid RDC 3339 (section 5.6) date; e.g. "2017-07-21T17:32:28Z".';
    }

    /**
     * @return ParameterValidationRuleInterface
     */
    protected function getBaseRule(): ParameterValidationRuleInterface
    {
        return new ParameterValidationRule(
            Validator::callback(function (string $value) {
                try {
                    return (bool) CarbonImmutable::createFromFormat(static::DATE_FORMAT, $value);
                } catch (InvalidArgumentException $e) {
                    return false;
                }
            })
        );
    }
}
