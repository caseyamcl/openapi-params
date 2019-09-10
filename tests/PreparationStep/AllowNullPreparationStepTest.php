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

namespace Paramee\PreparationStep;

use PHPUnit\Framework\TestCase;

class AllowNullPreparationStepTest extends TestCase
{
    /**
     * Tests that the Allow Null preparation step returns the inner step's API documentation
     */
    public function testGetApiDocumentationReturnsInnerApiDocumentation(): void
    {
        $innerStep = new SanitizeStep();
        $outerStep = new AllowNullPreparationStep($innerStep);
        $this->assertSame($innerStep->getApiDocumentation(), $outerStep->getApiDocumentation());
    }
}
