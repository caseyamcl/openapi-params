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

namespace Paramee\Format;

use Respect\Validation\Validator;
use Paramee\Contract\PreparationStepInterface;
use Paramee\Model\AbstractParamFormat;
use Paramee\Model\ParameterValidationRule;
use Paramee\PreparationStep\CallbackStep;
use Paramee\Type\StringParameter;

/**
 * OpenAPI String Byte Format
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ByteFormat extends AbstractParamFormat
{
    public const TYPE_CLASS = StringParameter::class;
    public const NAME = 'byte';

    /**
     * Get built-in validation rules
     *
     * These are added to the validation preparation step automatically
     *
     * @return array|ParameterValidationRule[]
     */
    public function getValidationRules(): array
    {
        // Validate that the string is base64 encoded.  This may be an expensive operation.
        $callback = function (string $value): bool {
            // Check if there are valid base64 characters
            if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $value)) {
                return false;
            }

            // Decode the string in strict mode and check the results
            if (! $decoded = base64_decode($value, true)) {
                return false;
            }

            // Encode the string again
            return base64_encode($decoded) == $value;
        };


        return [
            new ParameterValidationRule(Validator::callback($callback), 'value must be base64-encoded')
        ];
    }

    /**
     * Get built-in parameter preparation steps
     *
     * These run after validation but before any custom preparation steps
     *
     * @return array|PreparationStepInterface[]
     */
    public function getPreparationSteps(): array
    {
        return [new CallbackStep(function (string $value): string {
            return base64_decode($value);
        }, 'base64 decode the value')];
    }

    /**
     * @return string|null
     */
    public function getDocumentation(): ?string
    {
        return "Value must a base64-encoded string.";
    }
}
