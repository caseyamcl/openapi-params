<?php

/**
 * Paramee Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/paramee
 * @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/openapi-params
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams;

use OpenApiParams\Contract\ParameterDeserializer;
use OpenApiParams\Model\ParameterList;
use OpenApiParams\ParamContext\ParamBodyContext;
use OpenApiParams\ParamContext\ParamHeaderContext;
use OpenApiParams\ParamContext\ParamPathContext;
use OpenApiParams\ParamContext\ParamQueryContext;
use Psr\Log\LoggerInterface;

/**
 * Class Paramee
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class OpenApiParams
{
    /**
     * @param LoggerInterface|null $logger
     * @return ParameterList
     */
    public static function queryParams(?LoggerInterface $logger = null): ParameterList
    {
        return new ParameterList('query', [], new ParamQueryContext($logger));
    }

    /**
     * @param LoggerInterface|null $logger
     * @param ParameterDeserializer|null $deserializer
     * @return ParameterList
     */
    public static function bodyParams(
        ?LoggerInterface $logger = null,
        ?ParameterDeserializer $deserializer = null
    ): ParameterList {
        return new ParameterList('body', [], new ParamBodyContext($deserializer, $logger));
    }

    /**
     * @param LoggerInterface|null $logger
     * @return ParameterList
     */
    public static function pathParams(?LoggerInterface $logger = null): ParameterList
    {
        return new ParameterList('path', [], new ParamPathContext($logger));
    }

    /**
     * @param LoggerInterface|null $logger
     * @return ParameterList
     */
    public static function headerParams(?LoggerInterface $logger = null): ParameterList
    {
        return new ParameterList('header', [], new ParamHeaderContext($logger));
    }
}
