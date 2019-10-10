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

use OpenApiParams\Contract\ParameterDeserializer;
use OpenApiParams\Model\ParameterValuesContext;
use Psr\Log\LoggerInterface;

/**
 * Class ParamBodyContext
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class ParamBodyContext extends ParameterValuesContext
{
    /**
     * ParamBodyContext constructor.
     *
     * @param ParameterDeserializer|null $deserializer
     * @param LoggerInterface|null $logger
     */
    public function __construct(?ParameterDeserializer $deserializer = null, ?LoggerInterface $logger = null)
    {
        parent::__construct('body', $deserializer, $logger);
    }
}
