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

namespace OpenApiParams\ParamContext;

use OpenApiParams\Model\ParameterValuesContext;
use OpenApiParams\Model\ParameterValuesContextTest;
use Psr\Log\LoggerInterface;

class ParamPathContextTest extends ParameterValuesContextTest
{
    protected function getContextInstance(LoggerInterface $logger = null): ParameterValuesContext
    {
        return new ParamPathContext($logger);
    }

    protected function getExpectedName(): string
    {
        return 'path';
    }
}
