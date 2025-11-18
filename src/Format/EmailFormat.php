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

namespace OpenApiParams\Format;

use OpenApiParams\Model\AbstractParamFormat;
use OpenApiParams\Model\ParameterValidationRule;
use OpenApiParams\Type\StringParameter;
use Symfony\Component\Validator\Constraints\Email;

class EmailFormat extends AbstractParamFormat
{
    public const string TYPE_CLASS = StringParameter::class;
    public const string NAME = 'email';

    public function getValidationRules(): array
    {
        return [
            new ParameterValidationRule(new Email(mode: 'strict'), 'value must be a valid email')
        ];
    }
}
