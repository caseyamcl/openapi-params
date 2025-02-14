<?php

/**
 * OpenApi-Params Library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/openapi-params
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace OpenApiParams\Utility;

/**
 * Class UnpackCSV
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
final class UnpackCSV
{
    /**
     * @var array<int,string>
     */
    private array $defaultSeparators;

    /**
     * @param string $value
     * @param array<int,string>|string $separators
     * @return array<int,string>
     */
    public static function un(string $value, array|string $separators = [',']): array
    {
        static $that;
        if (! $that) {
            $that = new UnpackCSV();
        }

        return $that->unpack($value, $separators);
    }

    /**
     * UnpackCSV constructor.
     * @param array<int,string>|string $separators
     */
    public function __construct(array|string $separators = [','])
    {
        $this->defaultSeparators = (array) $separators;
    }

    /**
     * Invoke
     *
     * @param string $value
     * @param array<int,string> $separators
     * @return array<int,string>
     */
    public function __invoke(string $value, ?array $separators = null): array
    {
        return $this->unpack($value, (array) $separators);
    }

    /**
     * @param string $value
     * @param array<int,string>|string $separators
     * @return array<int,string>
     */
    public function unpack(string $value, array|string $separators = ''): array
    {
        $separators = $separators ? (array) $separators : $this->defaultSeparators;

        // Normalize separators
        if (count($separators) > 1) {
            foreach (array_slice($separators, 1) as $sep) {
                $value = str_replace($sep, $separators[0], $value);
            }
        }

        // Read the CSV
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
