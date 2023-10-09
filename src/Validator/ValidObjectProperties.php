<?php

namespace OpenApiParams\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidObjectProperties extends Constraint
{
    public const VALID_OBJECT_PROPERTIES_ERROR = '75dbf6e9-fb53-449f-8eb8-6777fb12e55e';

    public string $message = 'value is missing required properties: "{{ missingProperties }}"';

    /**
     * @var array<int,string>
     */
    public array $requiredProperties = [];

    /**
     * @var string This should be the full path to the property (e.g., "/data/myarr[3]/myobject")
     */
    public string $propertyName = '';

    #[HasNamedArguments]
    public function __construct(
        array $requiredProperties,
        string $propertyName = '',
        array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
        $this->requiredProperties = $requiredProperties;
        $this->propertyName = $propertyName ?: 'objectProps';
    }
}
