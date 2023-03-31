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

namespace OpenApiParams\Model;

use OpenApiParams\Utility\FilterNull;
use Respect\Validation\Validator;

/**
 * Class AbstractNumericParameter
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class AbstractNumericParameter extends Parameter
{
    private ?float $minimum = null;

    private ?float $maximum = null;

    private bool $exclusiveMinimum = false;

    private bool $exclusiveMaximum = false;

    private ?float $multipleOf = null;

    public function getMinimum(): ?float
    {
        return $this->minimum;
    }

    public function setMinimum(?float $minimum): self
    {
        $this->minimum = $minimum;
        return $this;
    }

    public function getMaximum(): ?float
    {
        return $this->maximum;
    }

    public function setMaximum(?float $maximum): self
    {
        $this->maximum = $maximum;
        return $this;
    }

    /**
     * Alias for setMinimum()
     *
     * @param float|null $min
     * @return AbstractNumericParameter
     */
    public function min(?float $min = null): self
    {
        return $this->setMinimum($min);
    }

    /**
     * Alias for setMaximum()
     *
     * @param float|null $max
     * @return AbstractNumericParameter
     */
    public function max(?float $max = null): self
    {
        return $this->setMaximum($max);
    }

    public function isExclusiveMinimum(): bool
    {
        return $this->exclusiveMinimum;
    }

    /**
     * @param bool $exclusiveMinimum
     * @return static|AbstractNumericParameter
     */
    public function setExclusiveMinimum(bool $exclusiveMinimum): self
    {
        $this->exclusiveMinimum = $exclusiveMinimum;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExclusiveMaximum(): bool
    {
        return $this->exclusiveMaximum;
    }

    /**
     * @param bool $exclusiveMaximum
     * @return static|AbstractNumericParameter
     */
    public function setExclusiveMaximum(bool $exclusiveMaximum): self
    {
        $this->exclusiveMaximum = $exclusiveMaximum;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getMultipleOf(): ?float
    {
        return $this->multipleOf;
    }

    /**
     * @param float|null $multipleOf
     * @return static|AbstractNumericParameter
     */
    public function setMultipleOf(?float $multipleOf): self
    {
        $this->multipleOf = $multipleOf;
        return $this;
    }

    protected function listExtraDocumentationItems(): array
    {
        return array_merge(
            parent::listExtraDocumentationItems(),
            FilterNull::filterNull([
                'minimum' => $this->getMinimum(),
                'maximum' => $this->getMaximum(),
                'multipleOf' => $this->multipleOf,
                'exclusiveMinimum' => $this->exclusiveMinimum ?: null,
                'exclusiveMaximum' => $this->exclusiveMaximum ?: null
            ])
        );
    }


    protected function getBuiltInValidationRules(): array
    {
        if ($this->getMinimum() !== null) {
            $minMessage = sprintf(
                'value must be greater than%s: %s',
                $this->exclusiveMinimum ? null : ' or equal to',
                number_format($this->getMinimum())
            );

            $min = $this->getMinimum();
            $rules[] = new ParameterValidationRule(
                $this->exclusiveMinimum ? Validator::greaterThan($min) : Validator::min($min),
                $minMessage,
                false
            );
        }

        if ($this->getMaximum() !== null) {
            $maxMessage = sprintf(
                'value must be less than%s: %s',
                $this->exclusiveMaximum ? null : ' or equal to',
                number_format($this->getMaximum())
            );

            $max = $this->getMaximum();
            $rules[] = new ParameterValidationRule(
                $this->exclusiveMaximum ? Validator::lessThan($max) : Validator::max($max),
                $maxMessage,
                false
            );
        }
        if ($multipleOf = $this->getMultipleOf()) {
            $rules[] = new ParameterValidationRule(
                Validator::multiple((int) $multipleOf),
                sprintf('value must be a multiple of %s', number_format((int) $this->getMultipleOf())),
                false
            );
        }

        return $rules ?? [];
    }
}
