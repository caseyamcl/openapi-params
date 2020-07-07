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

namespace OpenApiParams\Utility;

use OpenApiParams\Behavior\ValidatorFactoryTrait;
use OpenApiParams\Validation\Rules\ValidEmailLocalPart;

/**
 * Class Initialize
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class Initializer
{
    use ValidatorFactoryTrait;

    /**
     * @var bool
     */
    private static $isInitialized = false;

    /**
     * Initializes namespaces in the Validator default factory
     */
    public static function initialize(): void
    {
        if (self::$isInitialized) {
            return;
        }

        $that = new self();
        $that->ensureNamespacesRegistered(new ValidEmailLocalPart());
        self::$isInitialized = true;
    }
}
