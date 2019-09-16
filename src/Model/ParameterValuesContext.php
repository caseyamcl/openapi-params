<?php
/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\Model;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Paramee\Contract\ParameterDeserializer;

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
    /**
     * @var string
     */
    private $name;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ParameterDeserializer|null
     */
    private $deserializer;

    /**
     * ParameterContext constructor.
     *
     * @param string $name Should be something like 'query', 'body', 'cookie', 'path', etc..
     * @param ParameterDeserializer|null $deserializer
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        string $name = 'values',
        ?ParameterDeserializer $deserializer = null,
        ?LoggerInterface $logger = null
    ) {
        $this->name = $name;
        $this->deserializer = $deserializer;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return LoggerInterface
     */
    final public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return string
     */
    final public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return ParameterDeserializer|null
     */
    final public function getDeserializer(): ?ParameterDeserializer
    {
        return $this->deserializer;
    }
}
