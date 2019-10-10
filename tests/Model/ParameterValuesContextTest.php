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

namespace OpenApiParams\Model;

use OpenApiParams\ParamDeserializer\StandardDeserializer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class ParameterValuesContextTest extends TestCase
{
    public function testToStringReturnsContextName()
    {
        $obj = $this->getContextInstance();
        $this->assertSame($obj->getName(), $obj->__toString());
    }

    public function testGetLoggerReturnsNullLoggerWhenNoLoggerSpecified()
    {
        $this->assertInstanceOf(NullLogger::class, $this->getContextInstance()->getLogger());
    }

    public function testGetDeserializer()
    {
        $this->assertInstanceOf(StandardDeserializer::class, $this->getContextInstance()->getDeserializer());
    }

    public function testGetName()
    {
        $this->assertSame($this->getExpectedName(), $this->getContextInstance()->getName());
    }

    protected function getContextInstance(LoggerInterface $logger = null): ParameterValuesContext
    {
        return new ParameterValuesContext('values', new StandardDeserializer(), $logger);
    }

    protected function getExpectedName(): string
    {
        return 'query';
    }
}
