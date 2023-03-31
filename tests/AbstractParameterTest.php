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

namespace OpenApiParams;

use InvalidArgumentException;
use LogicException;
use OpenApiParams\Model\ParameterValidationRule;
use PHPUnit\Framework\TestCase;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Model\ParameterValues;
use OpenApiParams\PreparationStep\AllowNullPreparationStep;
use OpenApiParams\PreparationStep\EnsureCorrectDataTypeStep;
use Respect\Validation\Rules\AlwaysValid;
use Respect\Validation\Validatable;

/**
 * Class AbstactParameterTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class AbstractParameterTest extends TestCase
{
    public function testParameterDocumentationContainsExpectedValues()
    {
        $doc = $this->getInstance('test')->getDocumentation();

        // The 'type' value should *always* exist in the documentation
        $this->assertArrayHasKey('type', $doc);
    }

    public function testStringReturnsName(): void
    {
        $this->assertSame($this->getInstance()->__toString(), $this->getInstance()->getName());
    }

    public function testGetName(): void
    {
        $this->assertNotEmpty($this->getInstance()->getName());
    }

    /**
     * @dataProvider typeCastDataProvider()
     * @param $value
     */
    public function testTypeCastWorksCorrectlyWhenEnabled($value): void
    {
        $param = $this->getInstance()->setAllowTypeCast(true);
        if ($types = $param->getPhpDataTypes()) {
            $this->assertContains(gettype($param->prepare($value)), $types);
        } else {
            $this->markTestSkipped('Skipping test (no explicit data type required for: ' . get_class($param));
        }
    }

    /**
     * @dataProvider typeCastDataProvider()
     * @param mixed $value
     */
    public function testTypeCastThrowsExceptionForInvalidTypeWhenDisabled($value): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(EnsureCorrectDataTypeStep::class);

        $param = $this->getInstance()->setAllowTypeCast(false);
        $param->prepare($value);
    }

    public function testNullValueThrowsExceptionIfDisabled()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(EnsureCorrectDataTypeStep::class);

        $param = $this->getInstance()->setAllowTypeCast(false);
        $param->prepare(null);
    }

    public function testNullValueWrapsPreparationStepsWhenEnabled()
    {
        $param = $this->getInstance()->setAllowTypeCast(false);
        $param->setNullable(true);

        $this->assertContainsOnlyInstancesOf(AllowNullPreparationStep::class, $param->getPreparationSteps());
    }

    public function testNullValueIsUnchangedWhenEnabled()
    {
        $param = $this->getInstance()->setAllowTypeCast(false);
        $param->setNullable(true);

        $this->assertSame(null, $param->prepare(null));
    }

    /**
     * @dataProvider validValueDataProvider
     */
    public function testEnumCheckRunsIfEnumPresent($value)
    {
        $param = $this->getInstance()->setAllowTypeCast(false);
        $param->setEnum($this->getTwoOrMoreValidValues());
        $this->assertEquals($value, $param->prepare($value));
    }

    public function testEnumCheckFailsIfEnumNotPresent()
    {
        $this->expectExceptionMessage(InvalidValueException::class);
        $this->expectExceptionMessage('value must be one of');

        $values = $this->getTwoOrMoreValidValues();
        $valueToTest = array_shift($values);

        $param = $this->getInstance()->setEnum($values);
        $param->prepare($valueToTest);
    }

    public function testDependencyTestRunsAndFailsIfMissingDependency()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('parameter can only be used when other parameter(s) are present: nonexistent');

        $param = $this->getInstance('test');
        $value = current($this->getTwoOrMoreValidValues());
        $param->addDependsOn('nonexistent');

        $allValues = new ParameterValues(['test' => $param]);
        $param->prepare($value, $allValues);
    }

    /**
     * When calling prepare() without passing in a context, there is no point in testing for
     * dependencies, since it is 100% guaranteed that they will not be present
     */
    public function testDependencyTestDoesNotRunWhenParameterValuesNotPassedToPrepare()
    {
        $param = $this->getInstance('test');
        $value = current($this->getTwoOrMoreValidValues());
        $param->addDependsOn('nonexistent');

        $this->assertEquals($value, $param->prepare($value));
    }

    public function testDependencyTestRunsAndFailsIfOtherNotAllowedParamPresent()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('parameter can not be used when other parameter(s) are present: foo');

        $param = $this->getInstance('test');
        $param->addDependsOnAbsenceOf('foo');
        $value = current($this->getTwoOrMoreValidValues());

        $allValues = new ParameterValues(['test' => $value, 'foo' => 'bar']);
        $param->prepare($value, $param->prepare($value, $allValues));
    }

    public function testGetDefault(): void
    {
        $param = $this->getInstance('test');
        $value = current($this->getTwoOrMoreValidValues());
        $param->setDefaultValue($value);
        $this->assertSame($value, $param->getDefault());
    }

    public function testSetDescription(): void
    {
        $param = $this->getInstance();
        $param->setDescription('test');
        $this->assertStringStartsWith('test', $param->getDescription());
    }

    /**
     * @param ParameterValidationRule|Validatable|callable $rule
     * @dataProvider validValidationRuleProvider
     */
    public function testAddValidationWithValidArguments($rule)
    {
        $param = $this->getInstance();
        $param->addValidation($rule);
        $this->assertTrue(true, 'test passed');
    }

    public function testSetExamples()
    {
        $param = $this->getInstance();
        $examples = $this->getTwoOrMoreValidValues();
        $param->setExamples($examples);
        $this->assertEquals($examples, $param->listExamples());
    }

    public function testSetDefaultThrowsExceptionWhenIsRequired()
    {
        $this->expectException(LogicException::class);
        $param = $this->getInstance();
        $param->makeRequired(true);
        $param->setDefaultValue(current($this->getTwoOrMoreValidValues()));
    }

    public function testSetRequiredThrowsExceptionIfDefaultIsSet()
    {
        $this->expectException(LogicException::class);
        $param = $this->getInstance();
        $param->setDefaultValue(current($this->getTwoOrMoreValidValues()));
        $param->makeRequired(true);
    }

    public function testSetDeprecatedSetsDocumentationAppropriately()
    {
        $param = $this->getInstance();
        $param->setDeprecated(true);
        $this->assertArrayHasKey('deprecated', $param->getDocumentation());
    }

    public function testSetWriteOnly()
    {
        $param = $this->getInstance();
        $param->setWriteOnly(true);
        $this->assertTrue($param->isWriteOnly());
    }

    public function testReadOnly()
    {
        $param = $this->getInstance();
        $param->setReadOnly(true);
        $this->assertTrue($param->isReadOnly());
    }

    // --------------------------------------------------------------
    // Data providers for built-in methods

    public function validValidationRuleProvider(): array
    {
        return [
            [new ParameterValidationRule(new AlwaysValid(), 'always valid')],
            [new AlwaysValid()],
            [function () {
                return true;
            }]
        ];
    }

    /**
     * @return array|array[]
     */
    public function validValueDataProvider(): array
    {
        return array_map(function ($value) {
            return [$value];
        }, $this->getTwoOrMoreValidValues());
    }

    /**
     * @return array|array[]
     */
    public function typeCastDataProvider(): array
    {
        return array_map(function ($value) {
            return [$value];
        }, $this->getValuesForTypeCastTest());
    }

    // --------------------------------------------------------------
    // Abstract methods

    /**
     * Get at-least two valid values (preferably three or more)
     *
     * @return array|mixed[]
     */
    abstract protected function getTwoOrMoreValidValues(): array;

    /**
     * Return values that are not the correct type, but can be automatically type-cast if that is enabled
     *
     * @return array|mixed[]  Values for type cast check
     */
    abstract protected function getValuesForTypeCastTest(): array;

    /**
     * @param string $name
     * @return Parameter
     */
    abstract protected function getInstance(string $name = 'test'): Parameter;
}
