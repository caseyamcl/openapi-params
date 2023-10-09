<?php

namespace OpenApiParams\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidObjectExtraPropertiesValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        // Sanity test
        if (! $constraint instanceof ValidObjectExtraProperties) {
            throw new UnexpectedTypeException($constraint, ValidObjectExtraProperties::class);
        }

        // Type check (also allow arrays)
        if (!is_object($value) && !is_array($value)) {
            throw new UnexpectedValueException($value, 'object');
        }

        $valueProps = array_keys((array) $value);
        $diff = array_diff($valueProps, $constraint->allowedProperties);
        if (count($diff) > 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ invalid }}', implode(', ', $diff))
                ->setParameter('{{ allowed }}', implode(', ', $constraint->allowedProperties))
                ->setCode(ValidObjectExtraProperties::INVALID_OBJECT_PROPERTIES_ERROR)
                ->addViolation();
        }
    }
}
