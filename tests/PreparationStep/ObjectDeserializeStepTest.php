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

namespace Paramee\PreparationStep;

use Paramee\Exception\InvalidParameterException;
use Paramee\Model\ParameterValues;
use Paramee\ParamContext\ParamQueryContext;
use Paramee\Type\ObjectParameter;
use PHPUnit\Framework\TestCase;
use stdClass;

class ObjectDeserializeStepTest extends TestCase
{
    public function testGetApiDocumentationReturnsNull(): void
    {
        $this->assertNull((new ObjectDeserializeStep())->getApiDocumentation());
    }

    public function testToStringReturnsExpectedMessage(): void
    {
        $this->assertStringContainsString(
            'deserializes value to object',
            (new ObjectDeserializeStep())->__toString()
        );
    }

    /**
     *
     */
    public function testInvokeReturnsPreparedObjectWithValidData(): void
    {
        $param = new ObjectParameter('test');
        $value = 'role=admin,firstName=Alex';
        $allValues = new ParameterValues(['test' => 'role=admin,firstName=Alex'], new ParamQueryContext());
        $prepared = $param->prepare($value, $allValues);
        $this->assertInstanceOf(stdClass::class, $prepared);
        $this->assertSame('admin', $prepared->role);
        $this->assertSame('Alex', $prepared->firstName);
    }

    /**
     *
     */
    public function testInvokeThrowsExceptionForObjectWithInvalidData(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('malformed string');
        $param = new ObjectParameter('test');
        $value = 'xx;;asdfread@#@#$&*#@$';
        $param->prepare($value, new ParameterValues(['test' => 'xx;;asdfread@#@#$&*#@$'], new ParamQueryContext()));
    }
}
