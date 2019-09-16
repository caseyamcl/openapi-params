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

namespace Paramee\Model;

use ArrayObject;
use Countable;
use Generator;
use IteratorAggregate;

/**
 * Parameter List contains an immutable list of parameters
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ParameterList implements IteratorAggregate, Countable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ArrayObject
     */
    private $items;

    /**
     * ParameterList constructor.
     *
     * @param string $name
     * @param iterable|Parameter[] $items
     */
    public function __construct(string $name, iterable $items = [])
    {
        $this->name = $name;
        $this->items = new ArrayObject();

        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    /**
     * @param Parameter $param
     */
    private function addItem(Parameter $param): void
    {
        $this->items[$param->__toString()] = $param;
    }

    /**
     * Get a copy of this list with the parameter added
     *
     * @param Parameter $parameter
     * @return ParameterList
     */
    public function withParameter(Parameter $parameter): ParameterList
    {
        $that = clone $this;
        $that->addItem($parameter);
        return $that;
    }

    /**
     * @return Generator|Parameter[]  Keys are parameter name
     */
    public function getIterator(): Generator
    {
        foreach ($this->items as $name => $value) {
            yield $name => $value;
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->items->count();
    }
}
