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

namespace OpenApiParams\ParamContext;

use OpenApiParams\Model\ParameterValuesContext;
use OpenApiParams\ParamDeserializer\StandardDeserializer;
use Psr\Log\LoggerInterface;

/**
 * Class ParamPathContext
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class ParamPathContext extends ParameterValuesContext
{
    public function __construct(?LoggerInterface $logger = null)
    {
        parent::__construct('path', new StandardDeserializer(), $logger);
    }
}
