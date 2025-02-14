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

namespace OpenApiParams\ParamContext;

use OpenApiParams\Model\ParameterValuesContext;
use OpenApiParams\Model\ParameterValuesContextTestBase;
use Psr\Log\LoggerInterface;

class ParamQueryContextTest extends ParameterValuesContextTestBase
{
    protected function getContextInstance(?LoggerInterface $logger = null): ParameterValuesContext
    {
        return new ParamQueryContext($logger);
    }

    protected function getExpectedName(): string
    {
        return 'query';
    }
}
