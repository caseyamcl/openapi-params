<?php
/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace Paramee;

use InvalidArgumentException;
use LogicException;
use Paramee\Model\ParameterValidationRule;
use PHPUnit\Framework\TestCase;
use Paramee\Exception\InvalidParameterException;
use Paramee\Model\Parameter;
use Paramee\Model\ParameterValues;
use Paramee\PreparationStep\AllowNullPreparationStep;
use Paramee\PreparationStep\EnsureCorrectDataTypeStep;
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
        $this->assertNotEmpty($this->getInstance()->__toString());
    }

    /**
     * @dataProvider typeCastDataProvider()
     * @param $value
     */
    public function testTypeCastWorksCorrectlyWhenEnabled($value): void
    {
        $param = $this->getInstance()->setAllowTypeCast(true);
        if ($type = $param->getPhpDataType()) {
            $this->assertSame($type, gettype($param->prepare($value)));
        } else {
            $this->markTestSkipped('Skipping test (no explicit data type required for: ' . get_class($param));
        }
    }

    /**
     * @dataProvider typeCastDataProvider()
     */
    public function testTypeCastThrowsExceptionForInvalidTypeWhenDisabled($value): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage(EnsureCorrectDataTypeStep::class);

        $param = $this->getInstance()->setAllowTypeCast(false);
        $param->prepare($value);
    }

    public function testNullValueThrowsExceptionIfDisabled()
    {
        $this->expectException(InvalidParameterException::class);
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
        $this->expectExceptionMessage(InvalidParameterException::class);
        $this->expectExceptionMessage('value must be one of');

        $values = $this->getTwoOrMoreValidValues();
        $valueToTest = array_shift($values);

        $param = $this->getInstance()->setEnum($values);
        $param->prepare($valueToTest);
    }

    public function testDependencyTestRunsAndFailsIfMissingDependency()
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('parameter can only be used when other parameter(s) are present: nonexistent');

        $param = $this->getInstance('test');
        $value = current($this->getTwoOrMoreValidValues());
        $param->addDependsOn('nonexistent');

        $param->prepare($value, new ParameterValues(['test' => $param]));
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
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('parameter can not be used when other parameter(s) are present: foo');

        $param = $this->getInstance('test');
        $param->addDependsOnAbsenceOf('foo');
        $value = current($this->getTwoOrMoreValidValues());

        $param->prepare($value, $param->prepare(
            $value,
            new ParameterValues(['test' => $value, 'foo' => 'bar'])
        ));
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

    public function testAddValidationRuleThrowsInvalidArgumentExceptionWhenInvalidDataPassed()
    {
        $this->expectException(InvalidArgumentException::class);
        $param = $this->getInstance();
        $param->addValidation('foo');
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
        $param->setRequired(true);
        $param->setDefaultValue(current($this->getTwoOrMoreValidValues()));
    }

    public function testSetRequiredThrowsExceptionIfDefaultIsSet()
    {
        $this->expectException(LogicException::class);
        $param = $this->getInstance();
        $param->setDefaultValue(current($this->getTwoOrMoreValidValues()));
        $param->setRequired(true);
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
            [function() { return true; }]
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
