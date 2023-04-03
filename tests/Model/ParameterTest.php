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
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Respect\Validation\Validator;

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
        $ruleTwo = Validator::alnum('_');
        $ruleThree = new ParameterValidationRule(Validator::length(null, 5), 'This rule has documentation');
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
        $this->expectExceptionMessage('These rules must pass for ');

        $ruleOne = fn ($value) => $value !== 'abcdefg';
        $ruleTwo = Validator::alnum('_');
        $ruleThree = new ParameterValidationRule(Validator::length(null, 5), 'This rule has documentation');
        $param = StringParameter::create('xyz')->addValidationRules($ruleOne, $ruleTwo, $ruleThree);
        $param->prepare('abcdefg');
    }
}
