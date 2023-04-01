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

namespace OpenApiParams\Validation\Rules;

use OpenApiParams\Validation\AbstractValidatorRuleTestBase;
use OpenApiParams\Validation\Exceptions\ValidEmailLocalPartException;

class ValidEmailLocalPartTest extends AbstractValidatorRuleTestBase
{
    public function testInvalidLocalPartThrowsException()
    {
        $this->expectException(ValidEmailLocalPartException::class);
        (new ValidEmailLocalPart())->assert('john..doe');
    }

    public function testValidLocalPartSucceeds()
    {
        $this->assertTrue((new ValidEmailLocalPart())->validate('john.doe'));
    }
}
