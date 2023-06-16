<?php

namespace OpenApiParams\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UnixPathValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof UnixPath) {
            throw new UnexpectedTypeException($constraint, UnixPath::class);
        }

        if ($value === null or $value === '') {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
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