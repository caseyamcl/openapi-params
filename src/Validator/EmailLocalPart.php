<?php

namespace OpenApiParams\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class EmailLocalPart extends Constraint
{
    public const INVALID_FORMAT_ERROR = '592b22d3-b3fa-4628-95e5-39e92c4076bd';

    public string $message = 'Value must be a valid local portion of email. You provided: "{{ value }}"';
}
