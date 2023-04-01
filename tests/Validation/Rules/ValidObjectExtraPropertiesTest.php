<?php

declare(strict_types=1);

namespace OpenApiParams\Validation\Rules;

use OpenApiParams\Validation\AbstractValidatorRuleTestBase;
use OpenApiParams\Validation\Exceptions\ValidObjectExtraPropertiesException;
use stdClass;

/**
 * Class ValidObjectExtraPropertiesTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidObjectExtraPropertiesTest extends AbstractValidatorRuleTestBase
{
    public function testValid()
    {
        $obj = new stdClass();
        $obj->a = 'a';
        $obj->b = 'b';

        $this->assertTrue((new ValidObjectExtraProperties(['a', 'b']))->validate($obj));
    }

    public function testInvalid()
    {
        $this->expectException(ValidObjectExtraPropertiesException::class);
        $this->expectExceptionMessageMatches('/invalid properties in (.+)?: (.+?); allowed properties: (.+)/');
        $obj = new stdClass();
        $obj->a = 'a';
        $obj->b = 'b';

        (new ValidObjectExtraProperties(['a']))->assert($obj);
    }

    public function testNonArrayOrObjectValuesReturnFalse()
    {
        $val = 1;
        $this->assertFalse((new ValidObjectExtraProperties([]))->validate($val));
    }
}
