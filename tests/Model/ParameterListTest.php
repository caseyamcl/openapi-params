<?php

/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @package caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace Paramee\Model;

use ArrayObject;
use Paramee\Exception\AggregateErrorsException;
use Paramee\Contract\ParameterException;
use Paramee\ParamContext\ParamQueryContext;
use Paramee\Type\ArrayParameter;
use Paramee\Type\BooleanParameter;
use Paramee\Type\IntegerParameter;
use Paramee\Type\StringParameter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ParameterListTest extends TestCase
{
    public function testConstructor(): void
    {
        $obj = new ParameterList('test');
        $this->assertInstanceOf(ParameterList::class, $obj);
    }

    public function testGetReturnsParameterWhenItExists(): void
    {
        $obj = new ParameterList('test');
        $param = $obj->addString('test');
        $this->assertInstanceOf(StringParameter::class, $param);
    }

    public function testGetThrowsRuntimeExceptionWhenParamDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Parameter not found');
        $obj = new ParameterList('test');
        $obj->get('nonexistent');
    }

    public function testGetName(): void
    {
        $obj = new ParameterList('test');
        $this->assertSame('test', $obj->getName());
    }

    public function testGetParameters(): void
    {
        $params = [
            new StringParameter('test'),
            new ArrayParameter('test2')
        ];

        $obj = new ParameterList('test', $params);
        $this->assertInstanceOf(ArrayObject::class, $obj->getParameters());
        $this->assertSame(2, $obj->count());
    }

    public function testGetContext(): void
    {
        $obj = new ParameterList('test', [], new ParamQueryContext());
        $this->assertInstanceOf(ParamQueryContext::class, $obj->getContext());
    }

    public function testCount(): void
    {
        $params = [
            new StringParameter('test'),
            new ArrayParameter('test2')
        ];

        $obj = new ParameterList('test', $params);
        $this->assertSame(2, $obj->count());
    }

    public function testPrepareWithUndefinedValuesAndStrictIsTrue(): void
    {
        $this->expectException(AggregateErrorsException::class);
        $this->expectExceptionMessage('Undefined parameter: test3');

        $params = [
            (new StringParameter('test')),
            (new ArrayParameter('test2'))
        ];

        $obj = new ParameterList('test', $params);
        $obj->prepare(['test' => 'a', 'test2' => ['a', 'b'], 'test3' => 't']);
    }

    public function testPrepareWithUndefinedValuesAndStrictIsFalse(): void
    {
        $params = [
            (new StringParameter('test')),
            (new ArrayParameter('test2'))
        ];

        $obj = new ParameterList('test', $params);
        $prepared = $obj->prepare(['test' => 'a', 'test2' => ['a', 'b'], 'test3' => 't'], false);
        $this->assertEquals(['test', 'test2', 'test3'], $prepared->listNames());
    }

    public function testPrepareWithMissingRequiredValues(): void
    {
        $this->expectException(AggregateErrorsException::class);
        $this->expectExceptionMessage('Missing required parameter: test2');

        $params = [
            (new StringParameter('test'))->makeRequired(),
            (new ArrayParameter('test2'))->makeRequired()
        ];

        $obj = new ParameterList('test', $params);
        $obj->prepare(['test' => 'a'], false);
    }

    public function testPrepareWithInvalidValues(): void
    {
        $params = [
            (new IntegerParameter('test'))->makeRequired(),
            (new BooleanParameter('test2'))->makeRequired()
        ];

        try {
            $obj = new ParameterList('test', $params);
            $obj->prepare(['test' => 'a', 'test2' => 'b']);
        } catch (AggregateErrorsException $e) {
            $this->assertStringContainsString('There were 2 validation errors', $e->getMessage());
            $this->assertEquals(2, $e->count());

            foreach ($e as $ex) {
                $this->assertInstanceOf(ParameterException::class, $ex);
            }

            return;
        }

        $this->fail('Should not have made it here');
    }

    public function testGetApiDocumentationReturnsEmptyArrayWhenNoParametersAreAdded(): void
    {
        $obj = new ParameterList('test');
        $this->assertSame([], $obj->getApiDocumentation());
    }

    public function testGetApiDocumentationReturnsExpectedValuesWhenParametersAreAdded(): void
    {
        $obj = new ParameterList('test');
        $obj->addString('test1')
            ->makeRequired()
            ->setMinLength(5)
            ->setMaxLength(10)
            ->setDescription('here');

        $obj->addArray('test2')
            ->makeOptional()
            ->addAllowedParamDefinition(StringParameter::create()->makeOptional()->setDeprecated(true))
            ->addAllowedParamDefinition(IntegerParameter::create()->max(5));

        $this->assertSame([
            'test1' => [
                'type' => 'string',
                'required' => true,
                'description' => 'here',
                'minLength' => 5,
                'maxLength' => 10
            ],
            'test2' => [
                'type' => 'array',
                'items' => ['oneOf' => [
                    [
                        'type' => 'string',
                        'deprecated' => true
                    ],
                    [
                        'type' => 'integer',
                        'format' => 'int64',
                        'maximum' => 5.0
                    ]
                ]]
            ]
        ], $obj->getApiDocumentation());
    }

    public function testGetIterator(): void
    {
        $params = [new IntegerParameter('test'), new BooleanParameter('test2')];
        $obj = new ParameterList('test', $params);
        foreach ($obj as $item) {
            $this->assertInstanceOf(Parameter::class, $item);
        }
    }
}
