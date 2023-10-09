<?php

namespace OpenApiParams\Validator;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class EmailLocalPartValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        // Sanity test
        if (! $constraint instanceof EmailLocalPart) {
            throw new UnexpectedTypeException($constraint, EmailLocalPart::class);
        }

        if ($value === null or $value === '') {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $emailValidator = new EmailValidator();
        if (! $emailValidator->isValid($value . '@example.org', new NoRFCWarningsValidation())) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(EmailLocalPart::INVALID_FORMAT_ERROR)
                ->addViolation();
        }
    }
}
