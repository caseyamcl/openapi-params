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

use InvalidArgumentException;
use OpenApiParams\AbstractParameterTestBase;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValues;
use OpenApiParams\Model\ParameterValuesContext;
use OpenApiParams\ParamDeserializer\StandardDeserializer;
use OpenApiParams\PreparationStep\ArrayItemsPreparationStep;
use OpenApiParams\PreparationStep\CallbackStep;
use OpenApiParams\PreparationStep\ValidationStep;
use stdClass;

class ArrayParameterTest extends AbstractParameterTestBase
{
    public function testHeterogeneousTypesAllowedWhenNoTypesAdded()
    {
        $value = ['hi', 25, 34.0, [1,2,3]];
        $param = $this->buildInstance();
        $this->assertSame($value, $param->prepare($value));
    }

    public function testSetAllowedParamDefinitionMultipleWithValidValues()
    {
        $param = $this->buildInstance()
            ->addAllowedParamDefinition(new StringParameter(''))
            ->addAllowedParamDefinition(new IntegerParameter(''));

        $this->assertSame(['hi', 25], $param->prepare(['hi', 25]));
    }

    public function testSetAllowedParamDefinitionSingleWithValidValues()
    {
        $subParam = (new StringParameter(''))->setTrim(true);
        $param = $this->buildInstance()->addAllowedParamDefinition($subParam);
        $this->assertSame(['test'], $param->prepare(['   test ']));
    }

    public function testSetAllowedParamDefinitionSingleWithInvalidValues()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ArrayItemsPreparationStep::class);

        $subParam = (new StringParameter(''))->setTrim(true);
        $param = $this->buildInstance()->addAllowedParamDefinition($subParam);
        $param->prepare([1.2]);
    }

    public function testSetAllowedTypesWithValidTypesAndData()
    {
        $param = $this->buildInstance()->addAllowedType('string', 'integer');
        $this->assertEquals([25, 'test'], $param->prepare([25, 'test']));
    }

    public function testSetAllowedTypesThrowsExceptionWithNonExistentType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->buildInstance()->addAllowedType('string', 'xx');
    }

    public function testSetAllowedTypesThrowsExceptionWithValidTypesButInvalidData()
    {
        $this->expectException(InvalidValueException::class);
        $param = $this->buildInstance()->addAllowedType('string', 'integer');
        $param->prepare([25.35, 'test']);
    }

    public function testSetAllowedParamDefinitionWithInvalidValues()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid data type: string');

        $param = $this->buildInstance()->addAllowedParamDefinition(new IntegerParameter('item'));
        $param->prepare(['hi', 25]);
    }

    public function testAddPreparationStepForEachRulesAreRun()
    {
        $param = $this->buildInstance();
        $param->addPreparationStepForEach(new CallbackStep('strtoupper', 'Convert string to upper-case'));
        $this->assertSame(['A','B','C'], $param->prepare(['a', 'b', 'c']));
    }

    public function testEach()
    {
        $param = $this->buildInstance();
        $param->each(new CallbackStep('strtoupper', 'Convert string to upper-case'));
        $this->assertSame(['A','B','C'], $param->prepare(['a', 'b', 'c']));
    }

    public function testDescribeWithDefaultArguments()
    {
        $param = $this->buildInstance();
        $this->assertEquals(['type' => 'array', 'items' => new stdClass()], $param->getDocumentation());
    }

    public function testDescribeWithParameterSet()
    {
        $param = $this->buildInstance()
            ->addAllowedParamDefinition((new StringParameter(''))->setMinLength(5));

        $this->assertEquals(
            ['type' => 'array', 'items' => ['type' => 'string', 'minLength' => 5]],
            $param->getDocumentation()
        );
    }

    public function testDescribeWithMultipleParametersSet()
    {
        $param = $this->buildInstance()->addAllowedType('string', 'integer');
        $this->assertEquals(
            [
                'oneOf' => [
                    ['type' => 'string'],
                    ['type' => 'integer', 'format' => (new IntegerParameter())->getFormat()->__toString()]
                ]
            ],
            $param->getDocumentation()['items']
        );
    }

    public function testSetUniqueItemsWithUniqueItems()
    {
        $param = $this->buildInstance()->setUniqueItems(true);
        $this->assertEquals([2, 3, 5], $param->prepare([2, 3, 5]));
    }

    public function testSetUniqueItemsWithInvalidData()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);

        $param = $this->buildInstance()->setUniqueItems(true);
        $param->prepare([2, 2, 5]);
    }

    public function testSetMinItems()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);

        $param = $this->buildInstance()->setMinItems(3);
        $param->prepare([2, 2]);
    }

    public function testSetMaxItems()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(ValidationStep::class);

        $param = $this->buildInstance()->setMaxItems(3);
        $param->prepare([2, 2, 3, 4]);
    }

    public function testInvalidParameterExceptionReflectsPathOfItemCorrectly()
    {
        $param = $this->buildInstance()->addAllowedType('integer');

        try {
            $param->prepare([3, 9, -4, 'oops', 6, 'crud']);
            $this->fail('Expected exception: ' . InvalidValueException::class);
        } catch (InvalidValueException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('/test/3', current($errors)->getPointer());
            $this->assertEquals('/test/5', next($errors)->getPointer());
        }
    }

    public function testInvalidParameterInNestedObjectReflectsPathOfItemCorrectly()
    {
        $param = $this->buildInstance()->addAllowedParamDefinition(
            ObjectParameter::create()->addProperty(StringParameter::create('firstName')->setMinLength(30))
        );

        try {
            $param->prepare([
                (object) ['firstName' => 'Alakamarazmatazitsgettinglatesothisatestnameyouknow'],
                (object) ['firstName' => 'Bud'] // should fail
            ]);

            $this->fail('Expected exception: ' . InvalidValueException::class);
        } catch (InvalidValueException $e) {
            $this->assertEquals('/test/1/firstName', current($e->getErrors())->getPointer());
        }
    }

    public function testContextWithDeserializerDeserializesArray()
    {
        $context = (new ParameterValuesContext('test', new StandardDeserializer()));
        $parameter = $this
            ->buildInstance()
            ->addAllowedParamDefinition(IntegerParameter::create()->setAllowTypeCast(true));

        $allValues = ParameterValues::single(['1,2,3'], $context, 'test');
        $prepared = $parameter->prepare('1,2,3', $allValues);
        $this->assertSame([1, 2, 3], $prepared);
    }

    public function testPrepareThrowsInvalidParameterExceptionWihNoDeserializer()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage("invalid data type for parameter 'test'; expected: array; you provided: string");

        $context = (new ParameterValuesContext('test', null));
        $allValues = ParameterValues::single('1,2,3', $context, 'test');
        $this->buildInstance()->prepare('1,2,3', $allValues);
    }

    public function testPrepareThrowsInvalidParameterExceptionWithDeserializerButInvalidDataType()
    {
        $this->expectException(InvalidValueException::class);
        $context = (new ParameterValuesContext('test', new StandardDeserializer()));
        $allValues = ParameterValues::single(15.3, $context, 'test');
        $this->buildInstance()->prepare(15.3, $allValues);
    }

    // --------------------------------------------------------------

    /**
     * @return array
     */
    protected static function getTwoOrMoreValidValues(): array
    {
        return [
            [1, 2, 3],
            ['a', 'b', 'c'],
            [['a', 'b'], ['c', 'd'], ['e', 'f']]
        ];
    }

    /**
     * Return values that are not the correct type, but can be automatically type-cast if that is enabled
     *
     * @return array<int,mixed>  Values for type cast check
     */
    protected static function getValuesForTypeCastTest(): array
    {
        return ['a', 1, 35.0, true]; // non-array values will be type-cast
    }

    /**
     * @param string $name
     * @return ArrayParameter
     */
    protected function buildInstance(string $name = 'test'): ArrayParameter
    {
        return new ArrayParameter($name);
    }
}
