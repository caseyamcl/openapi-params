<?php
declare(strict_types=1);

namespace OpenApiParams\Utility;

use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Model\ParameterValues;
use OpenApiParams\PreparationStep\RespectValidationStep;
use OpenApiParams\Type\StringParameter;
use OpenApiParams\Validation\Rules\ValidEmailLocalPart;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Validator;

/**
 * @runTestsInSeparateProcesses
 */
class InitializerTest extends TestCase
{
    public function testInitializeRunWhenCreatingParameter(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('emailLocalPart must be a valid local portion of email');

        $strParam = new StringParameter('foo');
        $strParam->addValidation(new ValidEmailLocalPart());
        $strParam->prepare('test..test');
    }


    public function testInitializerNecessary(): void
    {
        $this->expectException(ComponentException::class);
        $this->expectExceptionMessage('is not a valid rule name');

        $rule = new ParameterValidationRule(
            Validator::anyOf(Validator::validEmailLocalPart(), Validator::email()),
            'is valid email or email local part'
        );

        $step = new RespectValidationStep([$rule]);
        $pv = new ParameterValues(['test' => 'my..email']);

        $step->__invoke('my..email', 'test', $pv);
    }
}
