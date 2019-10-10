<?php

/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @package caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace Paramee\Model;

use MJS\TopSort\CircularDependencyException;
use Paramee\ParamContext\ParamQueryContext;
use Paramee\Type\StringParameter;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

class ParameterTest extends TestCase
{
    public function testLoggingWorksCorrectly(): void
    {
        $logger = new TestLogger();
        $context = new ParamQueryContext($logger);

        $param = StringParameter::create('xyz')
            ->setMaxLength(10)
            ->setMinLength(3)
            ->setPattern('/[abc]/');

        $allValues = new ParameterValues(['xyz' => 'abc'], $context);
        $param->prepare('abc', $allValues);
        $this->assertSame(3, count($logger->records)); // There should have been three preparation steps

        $this->assertStringContainsString('ensure correct datatype', $logger->records[0]['message']);
        $this->assertStringContainsString('trims whitespace', $logger->records[1]['message']);
        $this->assertStringContainsString('runs the following validation', $logger->records[2]['message']);
    }

    /**
     * Test dependencies determine the processing order regardless of what order they are added in
     */
    public function testDependenciesAreOrderedCorrectly(): void
    {
        $params = new ParameterList('params');
        $params->addAlphaNumericValue('test')->addDependsOn('test1');
        $params->addInteger('test1')->addDependsOn('test2');
        $params->addBoolean('test2');

        $logger = new TestLogger();
        $allValues = new ParameterValues(
            ['test' => 'xyz', 'test1' => 15, 'test2' => false],
            new ParamQueryContext($logger)
        );
        $params->prepare($allValues);

        $orderOfOps = array_unique(array_map(function (array $logMessage) {
            return $logMessage['context']['name'];
        }, $logger->records));

        $this->assertSame(['test2', 'test1', 'test'], array_values($orderOfOps));
    }

    public function testDependencyLoopThrowsException(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency found');

        $params = new ParameterList('params');
        $params->addAlphaNumericValue('test')->addDependsOn('test1');
        $params->addInteger('test1')->addDependsOn('test2');
        $params->addBoolean('test2')->addDependsOn('test');

        $allValues = new ParameterValues(['test' => 'xyz', 'test1' => 15, 'test2' => false]);
        $params->prepare($allValues);
    }
}
