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

namespace Paramee\Type;

use InvalidArgumentException;
use LogicException;
use Respect\Validation\Validator;
use Paramee\Contract\ParamFormatInterface;
use Paramee\Model\Parameter;
use Paramee\Model\ParameterValidationRule;
use Paramee\PreparationStep\CallbackStep;
use Paramee\PreparationStep\SanitizeStep;
use Paramee\Utility\FilterNull;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * Class StringParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class StringParameter extends Parameter
{
    public const TYPE_NAME = 'string';
    public const PHP_DATA_TYPE = 'string';

    /**
     * @var string  Regex pattern without slashes
     */
    private $pattern;

    /**
     * @var int|null
     */
    private $minLength = null;

    /**
     * @var int|null
     */
    private $maxLength = null;

    /**
     * @var bool
     */
    private $trim = true;

    /**
     * @var bool
     */
    private $sanitize = false;

    /**
     * Set format
     *
     * @param ParamFormatInterface|null $format
     * @return self
     */
    public function setFormat(?ParamFormatInterface $format): self
    {
        if ($format && $format->appliesToType() !== static::class) {
            throw new LogicException(sprintf(
                "Cannot apply format %s to type %s (format only applies to type: %s) in parameter: %s",
                (string) $format,
                static::PHP_DATA_TYPE,
                $format->appliesToType(),
                $this->__toString()
            ));
        }

        $this->format = $format;
        return $this;
    }

    /**
     * Enable string sanitization (default is FALSE in case data is binary or you wish to use non-built in sanitization)
     * @param bool $sanitize
     * @return self
     */
    public function setSanitize(bool $sanitize): self
    {
        $this->sanitize = $sanitize;
        return $this;
    }

    /**
     * Explicitly turn trim on or off (default is TRUE; on)
     *
     * @param bool $trim
     * @return StringParameter
     */
    public function setTrim(bool $trim): self
    {
        $this->trim = $trim;
        return $this;
    }

    /**
     * Set regex pattern
     *
     * @param string|null $pattern  Can contain PHP '/' delimiters or not
     * @return StringParameter
     */
    public function setPattern(?string $pattern = null): self
    {
        $pattern = ($pattern{0} !== '/') ? '/' . $pattern . '/' : $pattern;

        // Test regex to ensure it is valid.
        try {
            preg_match($pattern, '');
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Pattern must be a valid regular expression', $e->getCode(), $e);
        }

        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @return array
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

    /**
     * @param int|null $length
     * @return StringParameter
     */
    public function setMinLength(?int $length): self
    {
        Assert::greaterThanEq($length, 0);

        $this->minLength = $length;
        return $this;
    }

    /**
     * @param int|null $length
     * @return StringParameter
     */
    public function setMaxLength(?int $length): self
    {
        Assert::greaterThanEq($length, 0);

        $this->maxLength = $length;
        return $this;
    }

    /**
     * Set both minimum and maximum allowable length
     *
     * @param int|null $min
     * @param int|null $max
     * @return StringParameter
     */
    public function setLength(?int $min = null, ?int $max = null): self
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
     * @return array
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
     * @return array
     */
    protected function getPostValidationPreparationSteps(): array
    {
        return $this->sanitize ? [new SanitizeStep()] : [];
    }

    /**
     * @return array
     */
    protected function getBuiltInValidationRules(): array
    {
        $rules = [];

        // Rules do not provide documentation, because that would be redundant in the API documentation
        if ($this->minLength !== null) {
            $rules[] = new ParameterValidationRule(Validator::length($this->minLength));
        }
        if ($this->maxLength !== null) {
            $rules[] = new ParameterValidationRule(Validator::length(null, $this->maxLength));
        }
        if ($this->pattern) {
            $rules[] = new ParameterValidationRule(Validator::regex($this->pattern));
        }

        return $rules;
    }
}
