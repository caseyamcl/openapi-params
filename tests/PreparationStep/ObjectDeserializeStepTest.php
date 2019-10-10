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

namespace OpenApiParams\PreparationStep;

use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValues;
use OpenApiParams\ParamContext\ParamQueryContext;
use OpenApiParams\Type\ObjectParameter;
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
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('malformed string');
        $param = new ObjectParameter('test');
        $value = 'xx;;asdfread@#@#$&*#@$';
        $allValues = new ParameterValues(['test' => 'xx;;asdfread@#@#$&*#@$'], new ParamQueryContext());
        $param->prepare($value, $allValues);
    }
}
