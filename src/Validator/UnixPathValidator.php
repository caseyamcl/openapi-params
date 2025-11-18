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

namespace OpenApiParams\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UnixPathValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        // Sanity test
        if (! $constraint instanceof UnixPath) {
            throw new UnexpectedTypeException($constraint, UnixPath::class);
        }

        if ($value === null or $value === '') {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($value === '/') {
            return;
        }

        $regex = ($constraint->allowRelativePath) ? '/^[\w\s\/]+$/' : '/^\/([\w\s\/]+)$/';
        $result = preg_match($regex, $value);

        if ($result !== 1) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(UnixPath::INVALID_FORMAT_ERROR)
                ->addViolation();
        }
    }
}
