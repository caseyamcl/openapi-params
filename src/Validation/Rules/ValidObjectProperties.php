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

namespace OpenApiParams\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

/**
 * Class ValidObjectProperties
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ValidObjectProperties extends AbstractRule
{
    /**
     * @var array<int,string>
     */
    private array $requiredProperties;
    protected string $missingProperties = '';

    public function __construct(array $requiredProps)
    {
        $this->requiredProperties = $requiredProps;
    }

    /**
     * TODO: Is there a way to pass the path to this error message in order to produce more useful error messages?
     * e.g. /data/people[2]/attributes
     */
    public function getName(): ?string
    {
        return 'objectProps';
    }

    public function validate($input): bool
    {
        if (!is_object($input) && !is_array($input)) {
            return false;
        }

        $inputKeys = array_keys((array) $input);
        $diff = array_diff($this->requiredProperties, $inputKeys);
        if (count($diff) === 0) {
            return true;
        } else {
            $this->missingProperties = implode(', ', $diff);
            return false;
        }
    }
}
