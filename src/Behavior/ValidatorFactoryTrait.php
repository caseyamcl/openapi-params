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

namespace OpenApiParams\Behavior;

use ReflectionException;
use ReflectionObject;
use Respect\Validation\Factory;
use Respect\Validation\Rules\AbstractComposite;
use Respect\Validation\Validatable;

/**
 * Trait ValidatorFactoryTrait
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
trait ValidatorFactoryTrait
{
    /**
     * Work-around unless/until my pull request gets accepted (https://bit.ly/2Z1A5wu)
     *
     * Check if there is a rule & exception class for this validator class either in the registered namespaces,
     * or based on this pattern:
     *
     * - MyValidators
     *   - Exceptions
     *     - FooBarException
     *   - Validators
     *     - FooBar
     *
     * If it does not exist, then use the default exception.
     * If it exists, but the namespace is not registered, register it.
     * If it exists, and the namespace is registered, then just return.
     *
     * @param Validatable $rule
     * @return bool  TRUE if the exception namespace was registered, FALSE if not.
     * @throws ReflectionException
     */
    protected function ensureNamespacesRegistered(Validatable $rule): bool
    {
        $ensureNsRegistered = function (string $ns, string $propertyName): void {
            // Check if the namespace exists
            $factoryObj = Factory::getDefaultInstance();
            $reflectionFactoryObj = new ReflectionObject($factoryObj);
            $reflectionProp = $reflectionFactoryObj->getProperty($propertyName);
            $reflectionProp->setAccessible(true);
            $registeredNamespaces = $reflectionProp->getValue($factoryObj);

            // If the namespace is already registered in the array, then skip registering it again.
            if (! in_array($ns, $registeredNamespaces)) {
                if ($propertyName === 'rulesNamespaces') {
                    Factory::setDefaultInstance($factoryObj->withRuleNamespace($ns));
                } else {
                    Factory::setDefaultInstance($factoryObj->withExceptionNamespace($ns));
                }
            }
        };

        // If it is a composite rule, then be sure the namespaces are loaded for all sub-rules
        if ($rule instanceof AbstractComposite) {
            $exceptionNsRegistered = false;
            foreach ($rule->getRules() as $rl) {
                $exceptionNsRegistered = $this->ensureNamespacesRegistered($rl);
            }
            return $exceptionNsRegistered;
        }

        // Setup some variables
        $reflectionRuleObj = new ReflectionObject($rule);
        $ruleNamespace = $reflectionRuleObj->getNamespaceName();
        $exceptionClassExists = false;
        $exceptionNamespace = '';

        $ensureNsRegistered($ruleNamespace, 'rulesNamespaces');

        // Find the exception namespace and whether or not a class exists for the Exception.
        if (substr($ruleNamespace, -6) === '\Rules') {
            $exceptionNamespace = trim(str_replace("\\Rules", "\\Exceptions", $ruleNamespace), "\\");
            $exceptionClassName = $exceptionNamespace . "\\" . $reflectionRuleObj->getShortName() . "Exception";
            $exceptionClassExists = class_exists($exceptionClassName);
        }

        // if the exception class exists, ensure that namespace is registered and return TRUE; if not,
        // then return false
        if ($exceptionClassExists) {
            $ensureNsRegistered($exceptionNamespace, 'exceptionsNamespaces');
            return true;
        } else {
            return false;
        }
    }
}
