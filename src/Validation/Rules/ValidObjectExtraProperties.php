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

namespace OpenApiParams\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

/**
 * Class ValidObjectExtraProperties
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidObjectExtraProperties extends AbstractRule
{
    private array $allowedProperties;
    private string $allowed;
    protected string $invalidProperties = '';

    public function __construct(array $allowedProperties)
    {
        $this->allowedProperties = $allowedProperties;
        $this->allowed = implode(', ', $allowedProperties);
    }

    public function getName(): ?string
    {
        return 'objectExtraProps';
    }

    public function validate($input): bool
    {
        if (!is_object($input) && !is_array($input)) {
            return false;
        }

        $inputKeys = array_keys((array) $input);
        $diff = array_diff($inputKeys, $this->allowedProperties);
        if (count($diff) === 0) {
            return true;
        } else {
            $this->invalidProperties = implode(', ', $diff);
            return false;
        }
    }
}
