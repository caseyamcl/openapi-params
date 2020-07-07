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

declare(strict_types=1);

namespace OpenApiParams\PreparationStep;

use OpenApiParams\Behavior\ValidatorFactoryTrait;
use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Model\ParameterValues;
use OpenApiParams\Validation\Rules\ValidEmailLocalPart;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Rules\Ip;
use Respect\Validation\Validator;

class RespectValidationStepTest extends TestCase
{
    use ValidatorFactoryTrait;

    public function testRespectValidationStepBasicFunctionality(): void
    {
        $rule = new ParameterValidationRule(new Ip(), 'is valid IP');
        $step = new RespectValidationStep([$rule]);
        $pv = new ParameterValues(['test' => '127.0.0.1']);
        $result = $step->__invoke('127.0.0.1', 'test', $pv);
        $this->assertSame('127.0.0.1', $result);
    }

    public function testCompoundRule(): void
    {
        $this->expectException(InvalidValueException::class);

        $rule = new ParameterValidationRule(
            Validator::anyOf(Validator::ip(), Validator::domain(false)),
            'is valid IP or domain name'
        );
        $step = new RespectValidationStep([$rule]);
        $pv = new ParameterValues(['test' => '-test.com']);
        $step->__invoke('-test.com', 'test', $pv);
    }

    public function testCustomRule(): void
    {
        $this->expectException(InvalidValueException::class);

        $rule = new ParameterValidationRule(
            new ValidEmailLocalPart(),
            'is valid email local part'
        );
        $step = new RespectValidationStep([$rule]);
        $pv = new ParameterValues(['test' => 'my..email']);
        $step->__invoke('my..email', 'test', $pv);
    }

    public function testBuiltInRulesAndCustomRule(): void
    {
        $this->ensureNamespacesRegistered(new ValidEmailLocalPart());
        $this->expectException(InvalidValueException::class);

        $rule = new ParameterValidationRule(
            Validator::anyOf(Validator::validEmailLocalPart(), Validator::email()),
            'is valid email or email local part'
        );

        $step = new RespectValidationStep([$rule]);
        $pv = new ParameterValues(['test' => 'my..email']);

        $step->__invoke('my..email', 'test', $pv);
    }
}
