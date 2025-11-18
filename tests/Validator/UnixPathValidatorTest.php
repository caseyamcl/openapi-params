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

class UnixPathValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ConstraintValidatorInterface
    {
        return new UnixPathValidator();
    }

    public function testInvalidConstraintThrowsException(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('test@example.org', new Json());
    }

    public function testEmptyIsValid(): void
    {
        $this->validator->validate(null, new UnixPath());
        $this->assertNoViolation();
    }

    public function testNonStringThrowsException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(12.2, new UnixPath());
    }

    #[DataProvider('validDataProvider')]
    public function testValidValueIsValid(string $value, bool $allowRelativePath = false): void
    {
        $validationObj = new UnixPath();
        $validationObj->allowRelativePath = $allowRelativePath;
        $this->validator->validate($value, $validationObj);
        $this->assertNoViolation();
    }

    public static function validDataProvider(): iterable
    {
        return [
            ['/'],
            ['/path/to/thing'],
            ['__/___', true],
            ['relative/path', true]
        ];
    }

    #[DataProvider('invalidDataProvider')]
    public function testInvalidValueProducesViolation(string $value, bool $allowRelativePath = false): void
    {
        $validationObj = new UnixPath();
        $validationObj->allowRelativePath = $allowRelativePath;
        $this->validator->validate($value, $validationObj);

        $this->buildViolation($validationObj->message)
            ->setParameter('{{ value }}', "\"$value\"")
            ->setCode($validationObj::INVALID_FORMAT_ERROR)
            ->assertRaised();
    }

    public static function invalidDataProvider(): iterable
    {
        return [
            ['@'],
            ['123\/test'],
            ['test/test'] // relative path, but allowRelativePath is false
        ];
    }

}
