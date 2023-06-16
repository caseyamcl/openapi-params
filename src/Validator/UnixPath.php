<?php

namespace OpenApiParams\Validator;

use Symfony\Component\Validator\Constraint;

class UnixPath extends Constraint
{
    public const INVALID_FORMAT_ERROR = '8c0da339-3c0a-4194-8c23-c440cd6018c2';

    public string $message = '{{ string }} must be a valid UNIX path (you provided: "{{ value }}")';

    public bool $allowRelativePath = false;
}