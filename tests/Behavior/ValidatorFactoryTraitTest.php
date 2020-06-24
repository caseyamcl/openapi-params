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

namespace OpenApiParams\Behavior;

use OpenApiParams\Validation\Exceptions\ValidDomainNameWithLocalhostException;
use OpenApiParams\Validation\Rules\ValidDomainNameWithLocalhost;
use PHPUnit\Framework\TestCase;

class ValidatorFactoryTraitTest extends TestCase
{
    use ValidatorFactoryTrait;

    public function testEnsureNamespaceInFactory(): void
    {
        $this->expectException(ValidDomainNameWithLocalhostException::class);
        $this->ensureExceptionNamespaceForRule(new ValidDomainNameWithLocalhost());

        (new ValidDomainNameWithLocalhost(false))->assert('localhost');
    }
}
