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

namespace OpenApiParams\Format;

use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Type\StringParameter;

/**
 * OpenAPI Password Format (note: this format does not validate passwords)
 *
 * This format exists solely to provide hints to the UI that the value should be masked.
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class PasswordFormat extends AbstractParamFormat
{
    public const TYPE_CLASS = StringParameter::class;
    public const NAME = 'password';

    public function getValidationRules(): array
    {
        // There are no built-in rules for passwords.  Implementing libraries should add their own custom rules...
        return [];
    }
}
