<?php

namespace OpenApiParams\Utility;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorFactory
{
    public static function build(): ValidatorInterface
    {
        $builder = Validation::createValidatorBuilder();
        $builder->enableAnnotationMapping();
        return $builder->getValidator();
    }
}
