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
class UndefinedParametersException extends ParameterException
{
    public function __construct(array $paramNames, $code = 422, Throwable $previous = null)
    {
        $message = ((count($paramNames) === 1) ? "Undefined parameter: " : 'Undefined parameters: ')
            . implode(', ', $paramNames);

        parent::__construct($message, $code, $previous);
    }
}