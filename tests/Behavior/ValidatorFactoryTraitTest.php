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

use OpenApiParams\Validation\Exceptions\ValidEmailLocalPartException;
use OpenApiParams\Validation\Rules\ValidEmailLocalPart;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Exceptions\ValidatorException;
use Respect\Validation\Validator;

class ValidatorFactoryTraitTest extends TestCase
{
    use ValidatorFactoryTrait;

    public function testEnsureNamespaceInFactoryRegistersRuleNamespace(): void
    {
        $this->ensureNamespacesRegistered(new ValidEmailLocalPart());

        $msgs = [];
        try {
            (Validator::validEmailLocalPart())->assert('test..test');
        } catch (ValidatorException $e) {
            $msgs = $e->getMessages();
        }

        $this->assertArrayHasKey('emailLocalPart', $msgs);
    }

    public function testEnsureNamespaceInFactoryRegistersExceptionNamespace(): void
    {
        $this->expectException(ValidEmailLocalPartException::class);
        $this->ensureNamespacesRegistered(new ValidEmailLocalPart());

        (new ValidEmailLocalPart())->assert('test..test');
    }
}
