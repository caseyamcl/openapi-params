<?php

/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\Exception;

use OpenApiParams\Behavior\ParameterErrorsTrait;
use OpenApiParams\Contract\ParameterException;
use OpenApiParams\Model\ParameterError;
use RuntimeException;
use Throwable;

/**
 * Class MissingParameterException
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MissingParameterException extends RuntimeException implements ParameterException
{
    use ParameterErrorsTrait;

    public function __construct(string $paramName, int $code = 422, ?Throwable $previous = null)
    {
        $error = new ParameterError('Missing required parameter: ' . $paramName, $paramName);
        $this->addError($error);
        parent::__construct($error->getTitle(), $code, $previous);
    }
}
