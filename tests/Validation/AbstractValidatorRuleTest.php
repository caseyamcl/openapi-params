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

namespace OpenApiParams\Validation;

use OpenApiParams\Behavior\ValidatorFactoryTrait;
use OpenApiParams\Validation\Rules\ValidUnixPath;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractValidatorRuleTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class AbstractValidatorRuleTest extends TestCase
{
    use ValidatorFactoryTrait;

    protected function setUp(): void
    {
        $this->ensureNamespacesRegistered(new ValidUnixPath());
        parent::setUp();
    }

}