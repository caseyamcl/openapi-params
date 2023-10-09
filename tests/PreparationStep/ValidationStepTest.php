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

use OpenApiParams\Exception\InvalidValueException;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Model\ParameterValues;
use OpenApiParams\Validator\EmailLocalPart;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Hostname;
use Symfony\Component\Validator\Constraints\Ip;

class ValidationStepTest extends TestCase
{
    public function testRespectValidationStepBasicFunctionality(): void
    {
        $rule = new ParameterValidationRule(new Ip(), 'is valid IP');
        $step = new ValidationStep([$rule]);
        $pv = new ParameterValues(['test' => '127.0.0.1']);
        $result = $step->__invoke('127.0.0.1', 'test', $pv);
        $this->assertSame('127.0.0.1', $result);
    }

    public function testCompoundRule(): void
    {
        $this->expectException(InvalidValueException::class);

        $rule = new ParameterValidationRule(
            new AtLeastOneOf([new Ip(), new Hostname()]),
            'is valid IP or domain name'
        );
        $step = new ValidationStep([$rule]);
        $pv = new ParameterValues(['test' => '-test.com']);
        $step->__invoke('-test.com', 'test', $pv);
    }

    public function testCustomRule(): void
    {
        $this->expectException(InvalidValueException::class);

        $rule = new ParameterValidationRule(
            new EmailLocalPart(),
            'is valid email local part'
        );
        $step = new ValidationStep([$rule]);
        $pv = new ParameterValues(['test' => 'my..email']);
        $step->__invoke('my..email', 'test', $pv);
    }
}
