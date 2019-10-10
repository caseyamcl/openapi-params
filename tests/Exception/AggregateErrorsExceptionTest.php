<?php

/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @package caseyamcl/openapi-params
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace OpenApiParams\Exception;

use OpenApiParams\Contract\ParameterException;
use OpenApiParams\Model\ParameterError;
use PHPUnit\Framework\TestCase;

class AggregateErrorsExceptionTest extends TestCase
{

    public function testGetIterator()
    {
        $exception = $this->getInstance();
        $this->assertContainsOnlyInstancesOf(ParameterException::class, $exception->getIterator());
    }

    public function testGetErrors()
    {
        $errors = $this->getInstance()->getErrors();
        $this->assertContainsOnlyInstancesOf(ParameterError::class, $errors);
    }

    public function testCount()
    {
        $this->assertSame(3, $this->getInstance()->count());
    }

    /**
     * @param array $exceptions Empty array means use default
     * @return AggregateErrorsException
     */
    protected function getInstance(array $exceptions = []): AggregateErrorsException
    {
        // Defaults
        if (empty($exceptions)) {
            $exceptions[] = new MissingParameterException('test');
            $exceptions[] = new MissingParameterException('test2');
            $exceptions[] = new UndefinedParametersException(['test3']);
        }

        return new AggregateErrorsException($exceptions);
    }
}
