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

namespace Paramee\Utility;

class UnpackCSV
{
    /**
     * @var array|string[]
     */
    private $defaultSeparators;

    /**
     * @param string $value
     * @param array|string[]|string $separators
     * @return array|string[]
     */
    public static function un(string $value, $separators = [','])
    {
        static $that;
        if (! $that) {
            $that = new static();
        }

        return $that->unpack($value, $separators);
    }

    /**
     * UnpackCSV constructor.
     * @param array|string[]|string $separators
     */
    public function __construct($separators = [','])
    {
        $this->defaultSeparators = (array) $separators;
    }

    /**
     * Invoke
     *
     * @param string $value
     * @param array $separators
     * @return array|\string[]
     */
    public function __invoke(string $value, $separators = null)
    {
        return $this->unpack($value, (array) $separators);
    }

    /**
     * @param string $value
     * @param array|string|string[] $separators
     * @return array|string[]
     */
    public function unpack(string $value, $separators = null)
    {
        $separators = $separators ? (array) $separators : $this->defaultSeparators;

        // Normalize separators
        if (count($separators) > 1) {
            foreach (array_slice($separators, 1) as $sep) {
                $value = str_replace($sep, $separators[0], $value);
            }
        }

        // Use fgetcsv to read the CSV
        $stream = fopen('php://memory', 'r+');
        $value = preg_replace("/[\r\n]+/", '', $value);
        fwrite($stream, $value);
        rewind($stream);
        $values = fgetcsv($stream, 0, $separators[0]);
        fclose($stream);

        // Trim off whitespace and filter empty values
        return array_values(array_filter(array_map('trim', $values ?: [])));
    }
}