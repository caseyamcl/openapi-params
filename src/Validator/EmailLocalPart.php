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

namespace OpenApiParams\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class EmailLocalPart extends Constraint
{
    public const string INVALID_FORMAT_ERROR = '592b22d3-b3fa-4628-95e5-39e92c4076bd';

    public string $message = 'Value must be a valid local portion of email. You provided: "{{ value }}"';
}
