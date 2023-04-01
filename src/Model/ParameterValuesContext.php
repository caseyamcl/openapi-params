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

namespace OpenApiParams\Model;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use OpenApiParams\Contract\ParameterDeserializer;

/**
 * Parameter Values context
 *
 * This class defines the context in which the parameters exist.
 *
 * This roughly corresponds to OpenAPI Parameter Types (path, query, header, cookie), but also
 * can be used for any parameter context (body, or whatever)
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ParameterValuesContext
{
    private LoggerInterface $logger;

    /**
     * ParameterContext constructor.
     *
     * @param string $name Should be something like 'query', 'body', 'cookie', 'path', etc..
     * @param ParameterDeserializer|null $deserializer
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        private readonly string $name = 'values',
        private readonly ?ParameterDeserializer $deserializer = null,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    final public function __toString(): string
    {
        return $this->name;
    }

    final public function getDeserializer(): ?ParameterDeserializer
    {
        return $this->deserializer;
    }
}
