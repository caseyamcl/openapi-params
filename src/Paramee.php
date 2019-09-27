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

declare(strict_types=1);

namespace Paramee;

use Paramee\Model\ParameterList;
use Paramee\Model\ParameterValuesContext;
use Paramee\ParamContext\ParamQueryContext;
use Psr\Log\LoggerInterface;

/**
 * Class Paramee
 * @package Paramee
 */
class Paramee
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
     * Paramee constructor.
     *
     * @param ParameterValuesContext|null $context
     */
    public function __construct(ParameterValuesContext $context = null)
    {

    }
}