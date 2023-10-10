<?php

use OpenApiParams\Format\AlphanumericFormat;
use OpenApiParams\Type\ArrayParameter;
use OpenApiParams\Type\IntegerParameter;
use OpenApiParams\Type\ObjectParameter;
use OpenApiParams\Type\StringParameter;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

require_once(__DIR__ . '/../vendor/autoload.php');

$rawData = '
{
  "data": {
    "id": "512",
    "type": "people",
    "attributes": {
      "displayName": "Alice Jones",
      "title": "CEO"
    },
    "relationships": {
      "books": { "data": [
        { "type": "books", "id": 52 },
        { "type": "books", "id": 31 }
      ] },
      "tags": { "data": [] },
      "spouse": { "data": { "type": "people", "id": 213 } },
      "bestFriend": { "data": null }
    }
  }
}
';

$testData = (object) [
    'data' => (object) [
        'id' => '561',
        'type' => 'people',
        'attributes' => (object) [
            'displayName' => 'Alice Grey',
            'title' => 'CEO'
        ],
        'relationships' => (object) [
            'books' => (object) ['data' => [
                (object) ['type' => 'books', 'id' => '53'],
                (object) ['type' => 'books', 'id' => '12'],
                (object) ['type' => 'books', 'id' => '20']
            ]],
            'spouse' => (object) ['data' => (object) ['type' => 'people', 'id' => 200]]
        ]
    ]
];

// Relationship parameter
$relationshipParam = fn($nullable = true) => ObjectParameter::create('data')->setNullable($nullable)->addProperties(
    StringParameter::create('type', true),
    IntegerParameter::create('id', true)->setAllowTypeCast(true)
);

$dataParam = ObjectParameter::create()->addProperty(
    ObjectParameter::create('data', true)->addProperties(
        StringParameter::create('id', true)->addValidationRule(new Regex('/^[0-9]+$/')),
        StringParameter::create('type', true)->setFormat(new AlphanumericFormat()),
        ObjectParameter::create('attributes')->addProperties(
            StringParameter::create('displayName')->setSanitize(true),
            StringParameter::create('title')
                ->addValidationRule(new Choice(['CEO', 'CIO', 'CFO']))
                ->addValidationRule(new Length(max: 3))
        ),
        ObjectParameter::create('relationships')->addProperties(
            // To-Many w/data
            ObjectParameter::create('books')->addProperty(
                ArrayParameter::create('data')->addAllowedParamDefinition($relationshipParam())
            ),
            // To-Many w/o data
            ObjectParameter::create('tags')->addProperty(
                ArrayParameter::create('data')->addAllowedParamDefinition($relationshipParam())
            ),
            // To-one w/data; nullable
            ObjectParameter::create('spouse')->addProperty($relationshipParam()),
            // To-one w/o data
            ObjectParameter::create('bestFriend')->addProperty($relationshipParam()),
            // Non-existent parameter (should succeed, because object params are optional by default)
            ObjectParameter::create('children')->addProperty(
                ArrayParameter::create('data')->addAllowedParamDefinition($relationshipParam())
            )
        )
    )
);

//var_export($dataParam->prepare(json_decode($rawData)));
var_export($dataParam->prepare($testData));
