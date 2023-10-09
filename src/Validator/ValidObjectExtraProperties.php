<?php

namespace OpenApiParams\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidObjectExtraProperties extends Constraint
{
    public const INVALID_OBJECT_PROPERTIES_ERROR = 'b504ef20-2a44-46f9-b3b7-039c6167f01e';

    public string $message = 'invalid properties in value: "{{ invalid }}"; allowed properties: "{{ allowed }}"';

    /**
     * @var array<int,string>
     */
    public array $allowedProperties = [];

    #[HasNamedArguments]
    public function __construct(array $allowedProperties, array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);
        $this->allowedProperties = $allowedProperties;
    }
}
