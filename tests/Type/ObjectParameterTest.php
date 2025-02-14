<?php

/**
 *  OpenApi-Params Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/openapi-params
 *  @package caseyamcl/openapi-params
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\Type;

use LogicException;
use OpenApiParams\AbstractParameterTestBase;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\PreparationStep\ValidationStep;

/**
 * Class ObjectParameterTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ObjectParameterTest extends AbstractParameterTestBase
{
    public function testSetSchemaName()
    {
        $param = $this->buildInstance()->setSchemaName('Something');
        $this->assertSame('Something', $param->getSchemaName());
    }

    public function testObjectWithNoExplicitPropertiesDefinedAllowsArbitraryParameters()
    {
        $param = $this->buildInstance();
        $this->assertEquals('item', $param->prepare((object) ['arbitrary' => 'item'])->arbitrary);
    }

    public function testObjectWithExplicitPropertiesDefinedDoesNotAllowArbitraryParametersByDefault()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('allowed properties: "name"');

        $param = $this->buildInstance()->addProperty(StringParameter::create('name'));
        $param->prepare((object) ['arbitrary' => 'item']);
    }

    public function testObjectWithExplicitPropertiesAndAllowExtraAllowsArbitraryParameters()
    {
        $param = $this->buildInstance()
            ->addProperty(StringParameter::create('name'))
            ->setAllowAdditionalProperties(true);

        $this->assertEquals(
            (object) ['extra' => 'thing', 'name' => 'bob'],
            $param->prepare((object) ['extra' => 'thing', 'name' => 'bob'])
        );
    }

    public function testSetMinPropertiesWithValidData()
    {
        $param = $this->buildInstance()->setMinProperties(2);

        $value = (object) ['test' => 'item', 'test2' => 'item'];
        $this->assertSame($value, $param->prepare($value));
    }

    public function testMinPropertiesWithInvalidData()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);

        $param = $this->buildInstance()->setMinProperties(5);
        $param->prepare((object) ['test' => 'item', 'test2' => 'item']);
    }

    public function testSetMaxPropertiesWithValidData()
    {
        $param = $this->buildInstance()->setMaxProperties(3);

        $value = (object) ['test' => 'item', 'test2' => 'item'];
        $this->assertSame($value, $param->prepare($value));
    }

    public function testMaxPropertiesWithInvalidData()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);

        $param = $this->buildInstance()->setMaxProperties(1);
        $param->prepare((object) ['test' => 'item', 'test2' => 'item']);
    }

    public function testNestedPropertiesArePreparedCorrectlyProcessedWithValidData()
    {
        $param = $this->buildInstance()->addProperty(
            ObjectParameter::create('person')
                ->addProperty(StringParameter::create('firstName')->setTrim(true))
                ->addProperty(IntegerParameter::create('age')->makeRequired())
        );

        $prepared = $param->prepare((object) [
            'person' => (object) ['firstName' => '  Bob ', 'age' => 25]
        ]);

        $this->assertEquals('Bob', $prepared->person->firstName);
        $this->assertEquals(25, $prepared->person->age);
    }

    public function testPropertiesThrowExceptionWithExpectedPointer()
    {
        $param = $this->buildInstance('test')->addProperty(
            StringParameter::create('firstName')->setMinLength(30)
        );

        try {
            $param->prepare((object) ['firstName' => 'Bob']);
            $this->fail('Expected exception: ' . InvalidValueException::class);
        } catch (InvalidValueException $e) {
            $this->assertEquals('/test/firstName', current($e->getErrors())->getPointer());
        }
    }

    public function testNestedPropertiesWithInvalidDataThrowExceptionWithExpectedPointer()
    {
        $param = $this->buildInstance('test')->addProperty(
            ObjectParameter::create('person')
                ->addProperty(StringParameter::create('firstName')->setMinLength(30))
                ->addProperty(IntegerParameter::create('age'))
        );

        try {
            $param->prepare((object) ['person' => (object) ['firstName' => ' Bob ']]);
            $this->fail('Expected exception: ' . InvalidValueException::class);
        } catch (InvalidValueException $e) {
            $this->assertEquals('/test/person/firstName', current($e->getErrors())->getPointer());
        }
    }

    public function testRequiredPropertiesProduceTheCorrectDocumentationFormat()
    {
        $param = $this->buildInstance()->addProperties(
            StringParameter::create('firstName')->makeRequired(true),
            IntegerParameter::create('age')->makeRequired(true)
        );

        $docs = $param->getDocumentation();
        $this->assertArrayNotHasKey('required', $docs['properties']['firstName']);
        $this->assertArrayNotHasKey('required', $docs['properties']['age']);
        $this->assertEquals(['firstName', 'age'], $docs['required']);
    }

    public function testRequiredPropertiesThrowExceptionWhenValueNotPresent()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('missing required properties: "firstName, age"');

        $param = $this->buildInstance()->addProperties(
            StringParameter::create('firstName')->makeRequired(true),
            IntegerParameter::create('age')->makeRequired(true)
        );

        $param->prepare((object) []);
    }

    public function testAddPropertyListAddsPropertiesToObject(): void
    {
        $propertyList = [
            StringParameter::create('firstName')->makeRequired(),
            IntegerParameter::create('age')->makeRequired()
        ];

        $param = $this->buildInstance()->addPropertyList($propertyList);

        $docs = $param->getDocumentation();
        $this->assertArrayNotHasKey('required', $docs['properties']['firstName']);
        $this->assertArrayNotHasKey('required', $docs['properties']['age']);
        $this->assertEquals(['firstName', 'age'], $docs['required']);
    }

    public function testAfterAddPropertyRuns(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Test');

        $getSubClass = function () {
            return new class () extends ObjectParameter {
                protected function afterAddProperty(Parameter $parameter): void
                {
                    throw new LogicException('Test');
                }
            };
        };

        $obj = $getSubClass();
        $obj->addProperty(new StringParameter('TestParam'));
    }

    // --------------------------------------------------------------

    protected static function getTwoOrMoreValidValues(): array
    {
        return [
            (object) ['foo' => 'bar', 'baz' => 'biz'],
            (object) ['name' => 'John Doe', 'age' => 37.2]
        ];
    }


    /**
     * Return values that are not the correct type, but can be automatically type-cast if that is enabled
     *
     * @return array<string,mixed>  Values for type cast check
     */
    protected static function getValuesForTypeCastTest(): array
    {
        return [
            ['test' => 'array', 'to' => 'object'],
            'string' // becomes property $scalar
        ];
    }

    /**
     * @param string $name
     * @return ObjectParameter
     */
    protected function buildInstance(string $name = 'test'): Parameter
    {
        return new ObjectParameter($name);
    }
}
