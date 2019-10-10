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
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $rawValue;

    /**
     * @var bool
     */
    private $prepared = false;

    /**
     * @var mixed
     */
    private $preparedValue;

    /**
     * ParameterValue constructor.
     *
     * @param string $name
     * @param mixed $rawValue
     */
    public function __construct(string $name, $rawValue)
    {
        $this->name = $name;
        $this->rawValue = $rawValue;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->rawValue;
    }

    /**
     * Does this contain a prepared value?
     *
     * @return bool
     */
    public function isPrepared(): bool
    {
        return (bool) $this->prepared;
    }

    /**
     * @return mixed
     */
    public function getPreparedValue()
    {
        if (! $this->prepared) {
            throw new RuntimeException("Parameter has not yet been prepared: {$this->name}");
        }
        return $this->preparedValue;
    }

    /**
     * Get a copy of this with a prepared value
     *
     * @param $preparedValue
     * @return ParameterValue
     */
    public function withPreparedValue($preparedValue): ParameterValue
    {
        $that = clone $this;
        $that->prepared = true;
        $that->preparedValue = $preparedValue;
        return $that;
    }
}
