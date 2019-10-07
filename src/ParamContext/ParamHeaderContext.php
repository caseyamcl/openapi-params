<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com> caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Paramee\ParamContext;

use Paramee\Model\ParameterValuesContext;
use Paramee\ParamDeserializer\StandardDeserializer;
use Psr\Log\LoggerInterface;

/**
 * Class ParamHeaderContext
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class ParamHeaderContext extends ParameterValuesContext
{
    public function __construct(?LoggerInterface $logger = null)
    {
        parent::__construct('header', new StandardDeserializer(), $logger);
    }
}
