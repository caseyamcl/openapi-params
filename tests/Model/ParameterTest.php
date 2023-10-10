<?php

/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 * @package caseyamcl/openapi-params
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\Model;

use MJS\TopSort\CircularDependencyException;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\ParamContext\ParamQueryContext;
use OpenApiParams\Type\StringParameter;
use PhpParser\Node\Param;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\Regex;
use ColinODell\PsrTestLogger\TestLogger;

class ParameterTest extends TestCase
{
    public function testLoggingWorksCorrectly(): void
    {
        $logger = new TestLogger();
        $context = new ParamQueryContext($logger);

        $param = StringParameter::create('xyz')
            ->setMaxLength(10)
            ->setMinLength(3)
            ->setPattern('/[abc]/');

        $allValues = new ParameterValues(['xyz' => 'abc'], $context);
        $param->prepare('abc', $allValues);
        $this->assertSame(3, count($logger->records)); // There should have been three preparation steps

        $this->assertStringContainsString('ensure correct datatype', $logger->records[0]['message']);
        $this->assertStringContainsString('trims whitespace', $logger->records[1]['message']);
        $this->assertStringContainsString('runs the following validation', $logger->records[2]['message']);
    }

    /**
     * Test dependencies determine the processing order regardless of what order they are added in
     */
    public function testDependenciesAreOrderedCorrectly(): void
    {
        $params = new ParameterList('params');
        $params->addAlphaNumeric('test')->addDependsOn('test1');
        $params->addInteger('test1')->addDependsOn('test2');
        $params->addBoolean('test2');

        $logger = new TestLogger();
        $allValues = new ParameterValues(
            ['test' => 'xyz', 'test1' => 15, 'test2' => false],
            new ParamQueryContext($logger)
        );
        $params->prepare($allValues);

        $orderOfOps = array_unique(array_map(function (array $logMessage) {
            return $logMessage['context']['name'];
        }, $logger->records));

        $this->assertSame(['test2', 'test1', 'test'], array_values($orderOfOps));
    }

    public function testDependenciesAreOrderedCorrectlyWithProcessAfter(): void
    {
        $params = new ParameterList('params');
        $params->addAlphaNumeric('test')
            ->addDependsOn('test1')
            ->addProcessAfter('test3');
        $params->addInteger('test1')->addDependsOn('test2');
        $params->addBoolean('test2');
        $params->addYesNo('test3');

        $logger = new TestLogger();
        $allValues = new ParameterValues(
            ['test' => 'xyz', 'test1' => 15, 'test2' => false, 'test3' => 'yes'],
            new ParamQueryContext($logger)
        );
        $params->prepare($allValues);

        $orderOfOps = array_unique(array_map(function (array $logMessage) {
            return $logMessage['context']['name'];
        }, $logger->records));

        $this->assertSame(['test2', 'test1', 'test3', 'test'], array_values($orderOfOps));
    }

    public function testDependencyLoopThrowsException(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency found');

        $params = new ParameterList('params');
        $params->addAlphaNumeric('test')->addDependsOn('test1');
        $params->addInteger('test1')->addDependsOn('test2');
        $params->addBoolean('test2')->addDependsOn('test');

        $allValues = new ParameterValues(['test' => 'xyz', 'test1' => 15, 'test2' => false]);
        $params->prepare($allValues);
    }

    public function testDependencyWithCallback(): void
    {
        $cbTest = false;

        $params = new ParameterList('params');
        $params->addAlphaNumeric('test')
            ->addDependsOn('test1', function () use (&$cbTest) {
                $cbTest = true;
            });
        $params->addInteger('test1');

        $allValues = new ParameterValues(['test' => 'xyz', 'test1' => 15]);
        $params->prepare($allValues);

        $this->assertTrue($cbTest);
    }

    public function testOptionalDependencyWithCallback(): void
    {
        $cbTest = false;

        $params = new ParameterList('params');
        $params->addAlphaNumeric('test')
            ->addProcessAfter('test1', function () use (&$cbTest) {
                $cbTest = true;
            });
        $params->addInteger('test1');

        $allValues = new ParameterValues(['test' => 'xyz', 'test1' => 15]);
        $params->prepare($allValues);

        $this->assertTrue($cbTest);
    }

    public function testOptionalDependencyIsActuallyOptional(): void
    {
        $params = new ParameterList('params');
        $params->addAlphaNumeric('test')
            ->addProcessAfter('test1')
            ->addProcessAfter('test2'); // test2 doesn't exist
        $params->addInteger('test1');

        $allValues = new ParameterValues(['test' => 'xyz', 'test1' => 15]);
        $preparedValues = $params->prepare($allValues);

        $this->assertEquals('xyz', $preparedValues->get('test')->getPreparedValue());
        $this->assertEquals(15, $preparedValues->get('test1')->getPreparedValue());
    }

    public function testGetDocumentationReturnsExpectedArray(): void
    {
        $param = StringParameter::create('xyz')
            ->setMaxLength(10)
            ->setMinLength(3)
            ->setPattern('/[abc]/')
            ->setDefaultValue('abc');

        $this->assertEquals(
            ['type' => 'string', 'pattern' => '/[abc]/', 'minLength' => 3, 'maxLength' => 10, 'default' => 'abc'],
            $param->getDocumentation()
        );
    }

    public function testAddMultipleValidationRulesWithValidData(): void
    {
        $ruleOne = fn ($value) => $value !== 'abc';
        $ruleTwo = new Regex('/[A-Za-z0-9_]+/');
        $ruleThree = new ParameterValidationRule(new Length(max: 5), 'This rule has documentation');
        $param = StringParameter::create('xyz')->addValidationRules($ruleOne, $ruleTwo, $ruleThree);
        $prepared = $param->prepare('def');

        $this->assertEquals('def', $prepared);
        $this->assertEquals(
            ['type' => 'string', 'description' => 'This rule has documentation'],
            $param->getDocumentation()
        );
    }

    public function testAddMultipleValidationRulesWithInvalidData(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('(invalid data)');

        $ruleOne = new NotEqualTo('abcdefg');
        $ruleTwo = new Regex('/[a-zA-Z0-9_]+/');
        $ruleThree = new ParameterValidationRule(new Length(max: 5), 'This rule has documentation');
        $param = StringParameter::create('xyz')->addValidationRules($ruleOne, $ruleTwo, $ruleThree);
        $param->prepare('abcdefg');
    }
}
