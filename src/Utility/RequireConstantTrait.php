<?php
/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\Utility;

use Webmozart\Assert\Assert;

/**
 * Class RequireConstantTrait
 * @author Casey McLaughlin <caseyamcl@gmail.com> Paramee\Utility
 */
trait RequireConstantTrait
{
    /**
     * @param string $name
     * @param string $message
     * @return mixed
     */
    protected function requireConstant(string $name, string $message = '')
    {
        $constantName = 'static::' . $name;
        $value = constant($constantName);

        $message = $message ?: sprintf(
            "missing required constant '%s' in class: %s",
            $name,
            get_called_class()
        );
        Assert::notEmpty($value, $message);

        return $value;
    }
}