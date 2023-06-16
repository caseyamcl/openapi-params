<?php

namespace OpenApiParams\Utility;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorFactoryTest extends TestCase
{
    public function testBuildReturnsValidValidator(): void
    {
        $this->assertInstanceOf(ValidatorInterface::class, ValidatorFactory::build());
    }
}
