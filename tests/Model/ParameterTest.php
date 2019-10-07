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

use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    // TODO: Test parameter dependencies here.
    // Possibly implement markj/topsort library
    // Then:
    // - Move preparation logic from the ParameterList class into the Paramee class (the current situation violates SOLID principles)
    // - (...and conflates service class logic with value object state)
    // - The Paramee::queryParams(), etc.. methods should return a `Paramee` instance, rather than a ParameterList instance
    // - The `addAlphanumeric`, etc. convenience methods should be in a trait or in the `Paramee` class.
    // - Add sorting logic (possibly via a method call)
    // - Update tests
    // - Return the ParameterList class to immutable state
}
