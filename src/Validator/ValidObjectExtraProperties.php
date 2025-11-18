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
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ValidObjectExtraProperties extends Constraint
{
    public const string INVALID_OBJECT_PROPERTIES_ERROR = 'b504ef20-2a44-46f9-b3b7-039c6167f01e';

    public string $message = 'invalid properties in value: "{{ invalid }}"; allowed properties: "{{ allowed }}"';

    /**
     * @var array<int,string>
     */
    public array $allowedProperties = [];

    #[HasNamedArguments]
    public function __construct(array $allowedProperties, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);
        $this->allowedProperties = $allowedProperties;
    }
}
