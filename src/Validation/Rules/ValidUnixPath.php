<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/Paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/Paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace Paramee\Validation\Rules;

use Respect\Validation\Rules\Regex;

/**
 * Class ValidUnixPath
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidUnixPath extends Regex
{
    /**
     * ValidUnixPath constructor.
     * @param bool $allowRelativePaths
     */
    public function __construct(bool $allowRelativePaths = false)
    {
        ($allowRelativePaths)
            ? parent::__construct('/^[\w\s\/]+$/')
            : parent::__construct('/^\/([\w\s\/]+)$/');
    }
}
