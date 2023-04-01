<?php

/**
 *  OpenApi-Params Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/openapi-params
 *
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

/**
 * Class ValidUnixPath
 *
 * Attempts to check if a string is a valid UNIX/POSIX path
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidUnixPath extends AbstractRule
{
    public function __construct(
        private readonly bool $allowRelativePaths = false
    ) {
    }

    public function getName(): ?string
    {
        return 'unixPath';
    }

    public function validate($input): bool
    {
        return Validator::regex(($this->allowRelativePaths) ? '/^[\w\s\/]+$/' : '/^\/([\w\s\/]+)$/')->validate($input);
    }
}
