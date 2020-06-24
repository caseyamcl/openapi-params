<?php

declare(strict_types=1);

namespace OpenApiParams\Validation\Rules;

use OpenApiParams\Validation\AbstractValidatorRuleTest;
use OpenApiParams\Validation\Exceptions\ValidObjectExtraPropertiesException;
use stdClass;

/**
 * Class ValidObjectExtraPropertiesTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidObjectExtraPropertiesTest extends AbstractValidatorRuleTest
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
}
