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
class ValidObjectProperties extends Constraint
{
    public const string VALID_OBJECT_PROPERTIES_ERROR = '75dbf6e9-fb53-449f-8eb8-6777fb12e55e';

    public string $message = 'value is missing required properties: "{{ missingProperties }}"';

    /**
     * @var array<int,string>
     */
    public array $requiredProperties = [];

    /**
     * @var string This should be the full path to the property (e.g., "/data/myArr[3]/myObject")
     */
    public string $propertyName = '';

    #[HasNamedArguments]
    public function __construct(
        array $requiredProperties,
        string $propertyName = '',
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
        $this->requiredProperties = $requiredProperties;
        $this->propertyName = $propertyName ?: 'objectProps';
    }
}
