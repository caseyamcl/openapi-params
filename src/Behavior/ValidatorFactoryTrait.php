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
     * Check if there is an exception class for this validator class either in the registered namespaces, or based on
     * this pattern:
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
     * @return bool  TRUE if the namespace already
     * @throws ReflectionException
     */
    protected function ensureExceptionNamespaceForRule(Validatable $rule): bool
    {
        // Setup some variables
        $reflectionRuleObj = new ReflectionObject($rule);
        $ruleNamespace = $reflectionRuleObj->getNamespaceName();
        $exceptionClassExists = false;
        $exceptionNamespace = '';

        // Find the exception namespace and whether or not a class exists for the Exception.
        if (substr($ruleNamespace, -6) === '\Rules') {
            $exceptionNamespace = trim(str_replace("\\Rules", "\\Exceptions", $ruleNamespace), "\\");
            $exceptionClassName = $exceptionNamespace . "\\" . $reflectionRuleObj->getShortName() . "Exception";
            $exceptionClassExists = class_exists($exceptionClassName);
        }

        // If the class does not exist, bail.
        if (! $exceptionClassExists) {
            return false;
        }

        // Check if the namespace exists
        $factoryObj = Factory::getDefaultInstance();
        $reflectionFactoryObj = new ReflectionObject($factoryObj);
        $reflectionProp = $reflectionFactoryObj->getProperty('exceptionsNamespaces');
        $reflectionProp->setAccessible(true);
        $registeredNamespaces = $reflectionProp->getValue($factoryObj);

        // If the namespace is already registered in the array, then skip registering it again.
        if (! in_array($exceptionNamespace, $registeredNamespaces)) {
            Factory::setDefaultInstance($factoryObj->withExceptionNamespace($exceptionNamespace));
        }

        // Return TRUE, because the namespace was already registered or newly registered
        return true;
    }
}