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

namespace Paramee\Type;

use InvalidArgumentException;
use LogicException;
use Paramee\AbstractParameterTest;
use Paramee\Exception\InvalidValueException;
use Paramee\Format;
use Paramee\Format\Int32Format;
use Paramee\Format\PasswordFormat;
use Paramee\Model\Parameter;
use PHPUnit\Framework\Error\Notice;

/**
 * Class StringParameterTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class StringParameterTest extends AbstractParameterTest
{
    /**
     * @return array|array[]
     */
    protected function getValuesForTypeCastTest(): array
    {
        return [1.0, true];
    }

    public function testSetTrim()
    {
        // Enabled by default
        $param = $this->getInstance();
        $prepared = $param->prepare('  some value  ');
        $this->assertEquals('some value', $prepared);

        // Disabled explicitly
        $param = $this->getInstance()->setTrim(false);
        $this->assertEquals('   some value ', $param->prepare('   some value '));
    }

    public function testSetMaxLength()
    {
        $this->expectException(InvalidValueException::class);
        $param = $this->getInstance()->setMaxLength(2);
        $this->assertArrayHasKey('maxLength', $param->getDocumentation());
        $param->prepare('some value');
    }

    public function testSetMinLength()
    {
        $this->expectException(InvalidValueException::class);
        $param = $this->getInstance()->setMinLength(25);
        $this->assertArrayHasKey('minLength', $param->getDocumentation());
        $param->prepare('some value');
    }

    public function testSetSanitize()
    {
        $param = $this->getInstance()->setSanitize(true);
        $prepared = $param->prepare("<p>This is ä 'test'</p>");
        $this->assertEquals('This is ä test', $prepared);
    }

    public function testSetLength()
    {
        $this->expectException(InvalidValueException::class);
        $param = $this->getInstance()->setLength(1, 3);
        $this->assertArrayHasKey('minLength', $param->getDocumentation());
        $this->assertArrayHasKey('maxLength', $param->getDocumentation());
        $param->prepare("<p>This is ä 'test'</p>");
    }

    public function testSetPatternWithPhpDelimiters()
    {
        $this->expectException(InvalidValueException::class);
        $param = $this->getInstance()->setPattern('/^abc$/');
        $this->assertArrayHasKey('pattern', $param->getDocumentation());
        $param->prepare('def');
    }

    public function testSetPatternWithoutPhpDelimiters()
    {
        $this->expectException(InvalidValueException::class);
        $param = $this->getInstance()->setPattern('^abc$');
        $param->prepare('def');
    }

    public function testSetPatternFailsWithInvalidRegexp()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getInstance()->setPattern('asd(');
    }

    public function testSetFormatWorksWithValidFormat()
    {
        $obj = $this->getInstance()->setFormat(new PasswordFormat());
        $this->assertInstanceOf(PasswordFormat::class, $obj->getFormat());
    }

    public function testSetFormatThrowsExceptionWithInvalidFormat()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot apply format');
        $this->getInstance()->setFormat(new Int32Format()); // Invalid!
    }

    public function testSetFormatGeneratesNoticeWhenFormatAlreadySet()
    {
        $this->expectException(Notice::class);
        $this->expectExceptionMessage('Format already set for parameter');

        $obj = $this->getInstance();
        $obj->setFormat(new Format\AlphanumericFormat());
        $obj->setFormat(new PasswordFormat());
    }

    /**
     * @dataProvider makeMethodDataProvider
     * @param string $methodName
     * @param string $expectedFormatClass
     */
    public function testMakeMethods(string $methodName, string $expectedFormatClass)
    {
        /** @var StringParameter $obj */
        $obj = call_user_func([$this->getInstance(), $methodName]);
        $this->assertInstanceOf($expectedFormatClass, $obj->getFormat());
    }

    public function makeMethodDataProvider(): array
    {
        return [
            ['makeAlphanumeric', Format\AlphanumericFormat::class],
            ['makeYesNo',        Format\YesNoFormat::class],
            ['makeByte',         Format\ByteFormat::class],
            ['makeDate',         Format\DateFormat::class],
            ['makeDateTime',     Format\DateTimeFormat::class],
            ['makeCsv',          Format\CsvFormat::class],
            ['makePassword',     Format\PasswordFormat::class],
            ['makeTemporal',     Format\TemporalFormat::class],
            ['makeUuid',         Format\UuidFormat::class]
        ];
    }

    protected function getTwoOrMoreValidValues(): array
    {
        return ['hi there', '45.23', 'stringval'];
    }

    /**
     * @param string $name
     * @return StringParameter
     */
    protected function getInstance(string $name = 'test'): Parameter
    {
        return new StringParameter($name);
    }
}
