<?php
/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/paramee
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

/**
 * Class ParamPathContext
 *
 * @package Paramee\ParamContext
 */
final class ParamPathContext extends ParameterValuesContext
{
    public function __construct()
    {
        parent::__construct('path', new StandardDeserializer());
    }
}
