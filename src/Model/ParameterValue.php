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

use RuntimeException;

/**
 * Represents a parameter value
 *
 * Immutable, but contains withPreparedValue() to add prepared value
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class ParameterValue
{
    private bool $isPrepared = false;
    private mixed $preparedValue;

    /**
     * ParameterValue constructor.
     *
     * @param string $name
     * @param mixed $rawValue
     */
    public function __construct(
        private readonly string $name,
        private readonly mixed $rawValue
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRawValue(): mixed
    {
        return $this->rawValue;
    }

    /**
     * Does this contain a prepared value?
     */
    public function isPrepared(): bool
    {
        return $this->isPrepared;
    }

    public function getPreparedValue(): mixed
    {
        if (! $this->isPrepared) {
            throw new RuntimeException("Parameter has not yet been prepared: $this->name");
        }
        return $this->preparedValue;
    }

    /**
     * Get a copy of this with a prepared value
     */
    public function withPreparedValue(mixed $preparedValue): ParameterValue
    {
        $that = clone $this;
        $that->isPrepared = true;
        $that->preparedValue = $preparedValue;
        return $that;
    }
}
