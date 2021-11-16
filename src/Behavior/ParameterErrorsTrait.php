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

namespace OpenApiParams\Behavior;

use OpenApiParams\Model\ParameterError;

/**
 * Trait ParameterErrorsTrait
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
trait ParameterErrorsTrait
{
    /**
     * @var array|ParameterError[]
     */
    private $errors = [];

    /**
     * @param ParameterError $error
     */
    protected function addError(ParameterError $error): void
    {
        $this->errors[$error->getPointer()] = $error;
    }

    /**
     * @return array|ParameterError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $pointerPrefix
     * @return array|ParameterError[]
     */
    public function getErrorsWithPointerPrefix(string $pointerPrefix): array
    {
        $addPrefix = function (ParameterError $e) use ($pointerPrefix): ParameterError {
            return $e->withPointer($pointerPrefix . $e->getPointer());
        };

        $arr = [];

        foreach ($this->getErrors() as $pointer => $error) {
            $newErr = $addPrefix($error);
            $arr[$newErr->getPointer()] = $newErr;
        }

        return $arr;
    }
}
