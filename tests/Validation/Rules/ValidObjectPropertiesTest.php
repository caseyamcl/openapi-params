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

namespace OpenApiParams\Validation\Rules;

use OpenApiParams\Validation\AbstractValidatorRuleTestBase;
use OpenApiParams\Validation\Exceptions\ValidObjectPropertiesException;
use stdClass;

/**
 * Class ValidObjectPropertiesTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidObjectPropertiesTest extends AbstractValidatorRuleTestBase
{
    public function testValid()
    {
        $obj = new stdClass();
        $obj->a = 'a';
        $obj->b = 'b';

        $this->assertTrue((new ValidObjectProperties(['a', 'b']))->validate($obj));
    }

    public function testInvalid()
    {
        $this->expectException(ValidObjectPropertiesException::class);
        $this->expectExceptionMessage('missing required properties:');
        $obj = new stdClass();
        $obj->a = 'a';
        $obj->b = 'b';

        (new ValidObjectProperties(['a', 'b', 'c']))->assert($obj);
    }

    public function testEmptyRequiredProperties()
    {
        $obj = new stdClass();
        $this->assertTrue((new ValidObjectProperties([]))->validate($obj));
    }

    public function testNonArrayOrObjectValuesReturnFalse()
    {
        $val = 1;
        $this->assertFalse((new ValidObjectProperties([]))->validate($val));
    }
}
