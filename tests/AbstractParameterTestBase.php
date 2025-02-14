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
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Model\ParameterValues;
use OpenApiParams\PreparationStep\AllowNullPreparationStep;
use OpenApiParams\PreparationStep\EnsureCorrectDataTypeStep;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\IsTrue;

/**
 * Class AbstactParameterTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class AbstractParameterTestBase extends TestCase
{
    public function testParameterDocumentationContainsExpectedValues(): void
    {
        $doc = $this->buildInstance('test')->getDocumentation();

        // The 'type' value should *always* exist in the documentation
        $this->assertArrayHasKey('type', $doc);
    }

    public function testStringReturnsName(): void
    {
        $this->assertSame($this->buildInstance()->__toString(), $this->buildInstance()->getName());
    }

    public function testGetName(): void
    {
        $this->assertNotEmpty($this->buildInstance()->getName());
    }

    /**
     * @dataProvider typeCastDataProvider()
     * @param $value
     */
    public function testTypeCastWorksCorrectlyWhenEnabled($value): void
    {
        $param = $this->buildInstance()->setAllowTypeCast(true);
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

        $param = $this->buildInstance()->setAllowTypeCast(false);
        $param->prepare($value);
    }

    public function testNullValueThrowsExceptionIfDisabled()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(EnsureCorrectDataTypeStep::class);

        $param = $this->buildInstance()->setAllowTypeCast(false);
        $param->prepare(null);
    }

    public function testNullValueWrapsPreparationStepsWhenEnabled()
    {
        $param = $this->buildInstance()->setAllowTypeCast(false);
        $param->setNullable(true);

        $this->assertContainsOnlyInstancesOf(AllowNullPreparationStep::class, $param->getPreparationSteps());
    }

    public function testNullValueIsUnchangedWhenEnabled()
    {
        $param = $this->buildInstance()->setAllowTypeCast(false);
        $param->setNullable(true);

        $this->assertSame(null, $param->prepare(null));
    }

    /**
     * @dataProvider validValueDataProvider
     */
    public function testEnumCheckRunsIfEnumPresent($value)
    {
        $param = $this->buildInstance()->setAllowTypeCast(false);
        $param->setEnum($this->getTwoOrMoreValidValues());
        $this->assertEquals($value, $param->prepare($value));
    }

    public function testEnumCheckFailsIfEnumNotPresent(): void
    {
        $this->expectExceptionMessage(InvalidValueException::class);
        $this->expectExceptionMessage('value must be one of');

        $values = $this->getTwoOrMoreValidValues();
        $valueToTest = array_shift($values);

        $param = $this->buildInstance()->setEnum($values);
        $param->prepare($valueToTest);
    }

    /**
     * @dataProvider validValueDataProvider
     */
    public function testEnumListsNullIfNullable(): void
    {
        $param = $this->buildInstance()->setAllowTypeCast(false);
        $param->setEnum($this->getTwoOrMoreValidValues());
        $param->setNullable(true);
        $this->assertContains(null, $param->getDocumentation()['enum']);
    }

    public function testDependencyTestRunsAndFailsIfMissingDependency()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('parameter can only be used when other parameter(s) are present: nonexistent');

        $param = $this->buildInstance('test');
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
        $param = $this->buildInstance('test');
        $value = current($this->getTwoOrMoreValidValues());
        $param->addDependsOn('nonexistent');

        $this->assertEquals($value, $param->prepare($value));
    }

    public function testDependencyTestRunsAndFailsIfOtherNotAllowedParamPresent()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('parameter can not be used when other parameter(s) are present: foo');

        $param = $this->buildInstance('test');
        $param->addDependsOnAbsenceOf('foo');
        $value = current($this->getTwoOrMoreValidValues());

        $allValues = new ParameterValues(['test' => $value, 'foo' => 'bar']);
        $param->prepare($value, $allValues);
    }

    public function testGetDefault(): void
    {
        $param = $this->buildInstance('test');
        $value = current($this->getTwoOrMoreValidValues());
        $param->setDefaultValue($value);
        $this->assertSame($value, $param->getDefault());
    }

    public function testSetDescription(): void
    {
        $param = $this->buildInstance();
        $param->setDescription('test');
        $this->assertStringStartsWith('test', $param->getDescription());
    }

    /**
     * @dataProvider validValidationRuleProvider
     */
    public function testAddValidationWithValidArguments(ParameterValidationRule|Constraint|Callback|callable $rule)
    {
        $param = $this->buildInstance();
        $param->addValidationRule($rule);
        $this->assertTrue(true, 'test passed');
    }

    public function testSetExamples(): void
    {
        $param = $this->buildInstance();
        $examples = $this->getTwoOrMoreValidValues();
        $param->setExamples($examples);
        $this->assertEquals($examples, $param->listExamples());
    }

    public function testSetDefaultThrowsExceptionWhenIsRequired(): void
    {
        $this->expectException(LogicException::class);
        $param = $this->buildInstance();
        $param->makeRequired(true);
        $param->setDefaultValue(current($this->getTwoOrMoreValidValues()));
    }

    public function testSetRequiredThrowsExceptionIfDefaultIsSet(): void
    {
        $this->expectException(LogicException::class);
        $param = $this->buildInstance();
        $param->setDefaultValue(current($this->getTwoOrMoreValidValues()));
        $param->makeRequired(true);
    }

    public function testSetDeprecatedSetsDocumentationAppropriately(): void
    {
        $param = $this->buildInstance();
        $param->setDeprecated(true);
        $this->assertArrayHasKey('deprecated', $param->getDocumentation());
    }

    public function testSetWriteOnly(): void
    {
        $param = $this->buildInstance();
        $param->setWriteOnly(true);
        $this->assertTrue($param->isWriteOnly());
    }

    public function testReadOnly(): void
    {
        $param = $this->buildInstance();
        $param->setReadOnly(true);
        $this->assertTrue($param->isReadOnly());
    }

    // --------------------------------------------------------------
    // Data providers for built-in methods

    public static function validValidationRuleProvider(): array
    {
        $alwaysValid = new Callback(fn ($value) => null);

        return [
            [new ParameterValidationRule($alwaysValid, 'always valid')],
            [$alwaysValid],
            [function () {
                return true;
            }]
        ];
    }

    /**
     * @return array|array[]
     */
    public static function validValueDataProvider(): array
    {
        return array_map(function ($value) {
            return [$value];
        }, static::getTwoOrMoreValidValues());
    }

    /**
     * @return array|array[]
     */
    public static function typeCastDataProvider(): array
    {
        return array_map(function ($value) {
            return [$value];
        }, static::getValuesForTypeCastTest());
    }

    // --------------------------------------------------------------
    // Abstract methods

    /**
     * Get at-least two valid values (preferably three or more)
     *
     * @return array
     */
    abstract protected static function getTwoOrMoreValidValues(): array;

    /**
     * Return values that are not the correct type, but can be automatically type-cast if that is enabled
     *
     * @return array Values for type cast check
     */
    abstract protected static function getValuesForTypeCastTest(): array;

    /**
     * Build an instance of this type
     */
    abstract protected function buildInstance(string $name = 'test'): Parameter;
}
