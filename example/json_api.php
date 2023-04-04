<?php

use OpenApiParams\Type\ArrayParameter;
use OpenApiParams\Type\IntegerParameter;
use OpenApiParams\Type\ObjectParameter;
use OpenApiParams\Type\StringParameter;
use Respect\Validation\Validator as v;

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

$dataParam = ObjectParameter::create()->addProperty(
    ObjectParameter::create('data', true)->addProperties(
        StringParameter::create('id', true)->addValidationRule(v::numericVal()),
        StringParameter::create('type', true)->addValidationRule(v::alnum('_')),
        ObjectParameter::create('attributes')->addProperties(
            StringParameter::create('displayName')->setSanitize(true),
            StringParameter::create('title')->addValidationRule(v::in(['CEO', 'CIO', 'CFO'])->length(null, 3))
        ),
        ObjectParameter::create('relationships')->addProperties(
            // To-Many w/data
            ObjectParameter::create('books')->addProperty(
                ArrayParameter::create('data')->addAllowedParamDefinition(ObjectParameter::create()->addProperties(
                    StringParameter::create('type', true),
                    IntegerParameter::create('id', true)
                ))
            ),
            // To-one w/data
            ObjectParameter::create('spouse')->addProperty(
                ObjectParameter::create('data')->setNullable(true)->addProperties(
                    StringParameter::create('type', true),
                    IntegerParameter::create('id', true)
                )
            ),
            // To-one w/o data
            ObjectParameter::create('bestFriend')->addProperty(
                ObjectParameter::create('data')->setNullable(true)->addProperties(
                    StringParameter::create('type', true),
                    IntegerParameter::create('id', true)
                )
            )
        )
    )
);

var_export($dataParam->prepare(json_decode($rawData)));
//var_export($dataParam->prepare($testData));
