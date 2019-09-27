<?php
/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @package caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\Exception;

use Throwable;

/**
 * Class UndefinedParameterException
 * @package Paramee\Exception
 */
class UndefinedParameterException extends ParameterException
{
    public function __construct(string $paramName, $code = 422, Throwable $previous = null)
    {
        $message = "Undefined parameter: " . $paramName;
        parent::__construct($message, $code, $previous);
    }
}