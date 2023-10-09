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

use PHPUnit\Framework\TestCase;
use OpenApiParams\Contract\ParamValidationRule;
use OpenApiParams\Contract\ParamFormat;
use OpenApiParams\Contract\PreparationStep;

abstract class AbstractParamFormatTestBase extends TestCase
{
    public function testGetName()
    {
        $constantName = sprintf("%s::NAME", get_class($this->getFormat()));
        if (defined($constantName)) {
            $this->assertEquals(constant($constantName), (string) $this->getFormat());
        } else {
            $this->markTestSkipped('No name constant was determined');
        }
    }

    public function testGetPreparationSteps()
    {
        $this->assertIsArray($this->getFormat()->getPreValidationPreparationSteps());
        $this->assertContainsOnlyInstancesOf(
            PreparationStep::class,
            $this->getFormat()->getPreValidationPreparationSteps()
        );
    }

    public function testAppliesToType()
    {
        $this->assertNotEmpty($this->getFormat()->appliesToType());
    }

    public function testGetValidationRules()
    {
        $this->assertIsArray($this->getFormat()->getValidationRules());
        $this->assertContainsOnlyInstancesOf(
            ParamValidationRule::class,
            $this->getFormat()->getValidationRules()
        );
    }

    /**
     * Test that a parameter with the given format contains the expected documentation
     */
    public function testExpectedDocumentationExists()
    {
        $param = $this->getParameterWithFormat();
        $expected = $param->getFormat()->getDocumentation();

        $this->assertStringContainsString((string) $expected, $param->getDescription());
    }

    // --------------------------------------------------------------

    /**
     * @return ParamFormat
     */
    abstract protected function getFormat(): ParamFormat;

    /**
     * @return Parameter
     */
    abstract protected function getParameterWithFormat(): Parameter;
}
