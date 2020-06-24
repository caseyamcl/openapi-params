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

use OpenApiParams\Behavior\ValidatorFactoryTrait;
use OpenApiParams\Validation\AbstractValidatorRuleTest;
use OpenApiParams\Validation\Exceptions\ValidDomainNameWithLocalhostException;

class ValidDomainNameTest extends AbstractValidatorRuleTest
{
    use ValidatorFactoryTrait;

    public function testLocalhostThrowsExceptionUnlessEnabledInConstructor()
    {
        $this->ensureExceptionNamespaceForRule(new ValidDomainNameWithLocalhost());

        $this->expectException(ValidDomainNameWithLocalhostException::class);
        (new ValidDomainNameWithLocalhost(false))->assert('localhost');
    }

    public function testLocalhostWorksWhenExplicitlyEnabledInConstructor()
    {
        $this->assertTrue((new ValidDomainNameWithLocalhost(true))->validate('localhost'));
    }
}
