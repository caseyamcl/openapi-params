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

namespace OpenApiParams\Type;

use DateTimeInterface;
use InvalidArgumentException;
use LogicException;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Contract\PreparationStep;
use OpenApiParams\Format;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\PreparationStep\CallbackStep;
use OpenApiParams\PreparationStep\SanitizeStep;
use OpenApiParams\Utility\FilterNull;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Webmozart\Assert\Assert;

/**
 * Class StringParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class StringParameter extends Parameter
{
    public const TYPE_NAME = 'string';
    public const PHP_DATA_TYPE = 'string';

    /**
     * @var string|null Regex pattern without slashes
     */
    private ?string $pattern = null;
    private ?int $minLength = null;
    private ?int $maxLength = null;
    private bool $trim = true;
    private bool $sanitize = false;

    // -----------------------------------------------------------------
    // Convenience methods

    /**
     * Set format to alphanumeric
     *
     * @param string $extraChars
     * @return self
     */
    final public function makeAlphanumeric(string $extraChars = ''): self
    {
        return $this->setFormat(new Format\AlphanumericFormat($extraChars));
    }

    /**
     * Set format to yes/no (accepts '1', '0', 1, 0, 'true', 'false', 'yes', 'no', 'on', 'off')
     * @return self
     */
    final public function makeYesNo(): self
    {
        return $this->setFormat(new Format\YesNoFormat());
    }

    /**
     * Set format to byte (base64-encoded)
     *
     * @return self
     */
    final public function makeByte(): self
    {
        return $this->setFormat(new Format\ByteFormat());
    }

    /**
     * Set format to email
     *
     * @return self
     */
    final public function makeEmail(): self
    {
        return $this->setFormat(new Format\EmailFormat());
    }

    /**
     * Set format to date
     *
     * Times are ignored; use StringParameter::makeDateTime() if you want to preserve the time
     *
     * @param DateTimeInterface|null $earliest
     * @param DateTimeInterface|null $latest
     * @return self
     */
    final public function makeDate(DateTimeInterface $earliest = null, DateTimeInterface $latest = null): self
    {
        return $this->setFormat(new Format\DateFormat($earliest, $latest));
    }

    /**
     * Set the format to date/time
     *
     * @param DateTimeInterface|null $earliest
     * @param DateTimeInterface|null $latest
     * @return self
     */
    final public function makeDateTime(DateTimeInterface $earliest = null, DateTimeInterface $latest = null): self
    {
        return $this->setFormat(new Format\DateTimeFormat($earliest, $latest));
    }

    /**
     * Set the format to password
     *
     * This format does nothing programmatically, but is important for API documentation in the case that clients
     * automatically parse the documentation to generate forms.
     *
     * @return self
     */
    final public function makePassword(): self
    {
        return $this->setFormat(new Format\PasswordFormat());
    }

    /**
     * Set the format to temporal value
     *
     * Accepts all date/time values and anything that can be parsed by Carbon
     * (e.g. "yesterday", "tomorrow", "today at 3pm", etc...)
     *
     * @param DateTimeInterface|null $earliest
     * @param DateTimeInterface|null $latest
     * @return self
     */
    final public function makeTemporal(DateTimeInterface $earliest = null, DateTimeInterface $latest = null): self
    {
        return $this->setFormat(new Format\TemporalFormat($earliest, $latest));
    }

    /**
     * Sets the format to UUID
     *
     * @return self
     */
    final public function makeUuid(): self
    {
        return $this->setFormat(new Format\UuidFormat());
    }

    final public function setFormat(?ParamFormat $format): self
    {
        if (! empty($this->format)) {
            trigger_error('Format already set for parameter: ' . $this->getName() ?: '(unnamed parameter)');
        }

        if ($format && $format->appliesToType() !== static::class) {
            throw new LogicException(sprintf(
                "Cannot apply format %s to type %s (format only applies to type: %s) in parameter: %s",
                $format,
                static::PHP_DATA_TYPE,
                $format->appliesToType(),
                $this->getName()
            ));
        }

        $this->format = $format;
        return $this;
    }

    /**
     * Enable string sanitization
     *
     * default is FALSE in case data is binary, or you wish to use non-built in sanitization
     *
     * @param bool $sanitize
     * @return self
     */
    final public function setSanitize(bool $sanitize): self
    {
        $this->sanitize = $sanitize;
        return $this;
    }

    /**
     * Explicitly turn trim on or off (default is TRUE; on)
     *
     * @param bool $trim
     * @return self
     */
    final public function setTrim(bool $trim): self
    {
        $this->trim = $trim;
        return $this;
    }

    /**
     * Set regex pattern
     *
     * @param string|null $pattern  Can contain PHP '/' delimiters or not
     * @return self
     */
    final public function setPattern(?string $pattern = null): self
    {
        $pattern = ($pattern[0] !== '/') ? '/' . $pattern . '/' : $pattern;

        // Test regex to ensure it is valid.
        if (@preg_match($pattern, '') === false) {
            throw new InvalidArgumentException('Pattern must be a valid regular expression');
        }

        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    protected function listExtraDocumentationItems(): array
    {
        return array_merge(
            parent::listExtraDocumentationItems(),
            FilterNull::filterNull([
                'pattern'   => $this->pattern,
                'minLength' => $this->minLength,
                'maxLength' => $this->maxLength
            ])
        );
    }

    final public function setMinLength(?int $length): self
    {
        Assert::greaterThanEq($length, 0);

        $this->minLength = $length;
        return $this;
    }

    final public function setMaxLength(?int $length): self
    {
        Assert::greaterThanEq($length, 0);

        $this->maxLength = $length;
        return $this;
    }

    /**
     * Set both minimum and maximum allowable length
     */
    final public function setLength(?int $min = null, ?int $max = null): self
    {
        if (! is_null($min)) {
            $this->setMinLength($min);
        }
        if (! is_null($max)) {
            $this->setMaxLength($max);
        }

        return $this;
    }

    /**
     * @return array<int,PreparationStep>
     */
    protected function getPreValidationPreparationSteps(): array
    {
        if (! $this->trim) {
            return [];
        }

        return [new CallbackStep('trim', 'trims whitespace from either side of the string')];
    }

    /**
     * Get built-in parameter preparation steps
     *
     * These run after validation but before format-specific preparation steps
     *
     * @return array<int,PreparationStep>
     */
    protected function getPostValidationPreparationSteps(): array
    {
        return $this->sanitize ? [new SanitizeStep()] : [];
    }

    /**
     * @return array<int,ParameterValidationRule>
     */
    protected function getBuiltInValidationRules(): array
    {
        $rules = [];

        // Rules do not provide documentation, because that would be redundant in the API documentation
        if ($this->minLength !== null) {
            $rules[] = new ParameterValidationRule(
                new Length(min: $this->minLength),
                sprintf('Ensure length no shorter than %s', number_format($this->minLength)),
                false
            );
        }
        if ($this->maxLength !== null) {
            $rules[] = new ParameterValidationRule(
                new Length(max: $this->maxLength),
                sprintf('Ensure length no longer than %s', number_format($this->maxLength)),
                false
            );
        }
        if ($this->pattern) {
            $rules[] = new ParameterValidationRule(
                new Regex($this->pattern),
                sprintf('Ensure pattern matches: "%s"', $this->pattern),
                false
            );
        }

        return $rules;
    }
}
