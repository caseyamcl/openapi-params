<?php

/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/openapi-params
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
 * Class UndefinedParameterException
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class UndefinedParametersException extends RuntimeException implements ParameterException
{
    use ParameterErrorsTrait;

    public function __construct(array $paramNames, $code = 422, Throwable $previous = null)
    {
        foreach ($paramNames as $paramName) {
            $this->addError(new ParameterError('Undefined parameter: ' . $paramName, $paramName));
        }

        $message = count($paramNames) === 1
            ? current($this->getErrors())->getTitle()
            : 'There were undefined parameters.';

        parent::__construct($message, $code, $previous);
    }
}
