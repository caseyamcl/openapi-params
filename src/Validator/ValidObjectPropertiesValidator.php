<?php

namespace OpenApiParams\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidObjectPropertiesValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        // Sanity test
        if (! $constraint instanceof ValidObjectProperties) {
            throw new UnexpectedTypeException($constraint, ValidObjectProperties::class);
        }

        // Type check (also allow arrays)
        if (!is_object($value) && !is_array($value)) {
            throw new UnexpectedValueException($value, 'object');
        }

        $valueProps = array_keys((array) $value);
        $diff = array_diff($constraint->requiredProperties, $valueProps);
        if (count($diff) > 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ missingProperties }}', implode(', ', $diff))
                ->setCode(ValidObjectProperties::VALID_OBJECT_PROPERTIES_ERROR)
                ->addViolation();
        }
    }
}
