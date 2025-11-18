<?php

/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class EmailLocalPartValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ConstraintValidatorInterface
    {
        return new EmailLocalPartValidator();
    }

    public function testInvalidConstraintThrowsException(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('test@example.org', new Json());
    }

    public function testEmptyIsValid(): void
    {
        $this->validator->validate(null, new EmailLocalPart());
        $this->assertNoViolation();
    }

    public function testNonStringThrowsException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(12.2, new EmailLocalPart());
    }

    #[DataProvider('invalidDataProvider')]
    public function testInvalidValueProducesViolation(string $value): void
    {
        $validationObj = new EmailLocalPart();
        $this->validator->validate($value, $validationObj);

        $this->buildViolation($validationObj->message)
            ->setParameter('{{ value }}', "\"$value\"")
            ->setCode($validationObj::INVALID_FORMAT_ERROR)
            ->assertRaised();
    }

    #[DataProvider('validDataProvider')]
    public function testValidValueIsValid(string $value): void
    {
        $this->validator->validate($value, new EmailLocalPart());
        $this->assertNoViolation();
    }

    public static function validDataProvider(): iterable
    {
        return [
            ['bob.jones'],
            ['123456789'],
            ['_________'],
            ['bob-jones'],
            ['bob+jones']
        ];
    }

    public static function invalidDataProvider(): iterable
    {
        return [
            ['test..invalid'],
            ['Joe Smith'],
            ['.test'],
            ['test.']
        ];
    }
}
