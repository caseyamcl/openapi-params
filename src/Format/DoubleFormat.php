<?php
/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\Format;

use RuntimeException;
use Paramee\Model\AbstractParamFormat;
use Paramee\Model\ParameterValidationRule;
use Paramee\Type\NumberParameter;

/**
 * OpenAPI Double Format
 *
 * This is automatically assigned based on the system's numeric precision capabilities
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DoubleFormat extends AbstractParamFormat
{
    public const TYPE_CLASS = NumberParameter::class;

    public function __construct()
    {
        if (PHP_FLOAT_DIG < 15) {
            throw new RuntimeException(sprintf(
                'This PHP installation does not support format: %s.  For more information, refer to: '
                . 'https://www.php.net/manual/en/language.types.float.php',
                get_called_class()
            ));
        }
    }

    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array|ParameterValidationRule[]
     */
    public function getValidationRules(): array
    {
        return []; // no extra validation rules
    }
}
