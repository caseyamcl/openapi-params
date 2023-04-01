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

namespace OpenApiParams\Format;

use OpenApiParams\Model\AbstractParamFormatTestBase;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Model\Parameter;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Type\StringParameter;
use Respect\Validation\Validator;

/**
 * Class CsvFormatTest
 * @package OpenApi-Params\Format
 */
class CsvFormatTest extends AbstractParamFormatTestBase
{
    public function testEach()
    {
        /** @var CsvFormat $format */
        $format = $this->getFormat();
        $validator = new ParameterValidationRule(Validator::equals('a'), 'test');
        $format->each($validator);
        $param = (new StringParameter('test'))->setFormat($format);
        $this->assertEquals(['a', 'a', 'a'], $param->prepare('a, a, a'));
    }

    public function testSetValidatorForEach()
    {
        /** @var CsvFormat $format */
        $format = $this->getFormat();
        $validator = new ParameterValidationRule(Validator::equals('a'), 'test');
        $format->setValidatorForEach($validator);
        $param = (new StringParameter('test'))->setFormat($format);
        $this->assertEquals(['a', 'a', 'a'], $param->prepare('a, a, a'));
    }

    public function testSetSeparator()
    {
        /** @var CsvFormat $format */
        $format = $this->getFormat();
        $format->setSeparators('|;');
        $param = (new StringParameter('test'))->setFormat($format);
        $this->assertSame(['test', 'test1', 'test2'], $param->prepare('test;test1|test2'));
    }

    /**
     * @dataProvider separatorInConstructorDataProvider
     * @param string|string[]|array $separator
     * @param string $values
     */
    public function testSetSeparatorInConstructor($separator, string $values): void
    {
        $format = new CsvFormat($separator);
        $param = (new StringParameter())->setFormat($format);
        $this->assertSame(['test1', 'test2', 'test3'], $param->prepare($values));
    }

    public function testGetDocumentationWhenMultipleSeparators()
    {
        $format = new CsvFormat(',|');
        $param = (new StringParameter())->setFormat($format);
        $this->assertSame(
            "Value must be a list of items delimited by one of the following: ',|'.",
            $param->getDocumentation()['description']
        );
    }

    public static function separatorInConstructorDataProvider(): array
    {
        return [
            [',', 'test1,test2,test3'],
            [',|', 'test1|test2,test3'],
            [',|', 'test1|test2,test3']
        ];
    }

    /**
     * @return ParamFormat
     */
    protected function getFormat(): ParamFormat
    {
        return new CsvFormat();
    }

    /**
     * @return Parameter
     */
    protected function getParameterWithFormat(): Parameter
    {
        return (new StringParameter('test'))->setFormat(new CsvFormat());
    }
}
