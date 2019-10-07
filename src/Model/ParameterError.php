<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\Model;

/**
 * Invalid Parameter Error
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ParameterError
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $detail;

    /**
     * @var int
     */
    private $code;

    /**
     * @var array
     */
    private $extra;

    /**
     * @var string
     */
    private $pointer;

    /**
     * InvalidParameterError constructor.
     * @param string $title    The error message
     * @param string $pointer  An array index or path (RFC6901) e.g. "paramName" or "data/relationships/people/3/id"
     * @param string $detail   The error detail
     * @param string $code     Application-specific error code
     * @param array $extra     Extra values
     */
    public function __construct(
        string $title,
        string $pointer,
        string $detail = '',
        string $code = '422',
        array $extra = []
    ) {
        $this->message = $title;
        $this->detail = $detail;
        $this->code = $code;
        $this->extra = $extra;
        $this->pointer = $pointer ? '/' . trim($pointer, '/') : '';
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getPointer(): string
    {
        return $this->pointer;
    }

    /**
     * @return string
     */
    public function getDetail(): string
    {
        return $this->detail;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->message;
    }

    /**
     * Get a copy of this object with a specified pointer
     *
     * @param string $pointer
     * @return ParameterError
     */
    public function withPointer(string $pointer): ParameterError
    {
        $that = clone $this;
        $that->pointer = $pointer ? '/' . trim($pointer, '/') : '';
        return $that;
    }
}
