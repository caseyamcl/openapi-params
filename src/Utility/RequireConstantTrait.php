<?php

declare(strict_types=1);

namespace Paramee\Utility;

use InvalidArgumentException;
use LogicException;
use Webmozart\Assert\Assert;

/**
 * Class RequireConstantTrait
 * @package Paramee\Utility
 */
trait RequireConstantTrait
{
    /**
     * @param string $name
     * @param string $message
     * @return mixed
     */
    protected function requireConstant(string $name, string $message = '')
    {
        $constantName = 'static::' . $name;

        try {
            $value = constant($constantName);

            $message = $message ?: sprintf(
                "missing required constant '%s' in class: %s",
                $name,
                get_called_class()
            );
            Assert::notEmpty($value, $message);

            return $value;
        } catch (InvalidArgumentException $e) {
            throw new LogicException($e->getMessage(), $e->getCode(), $e);
        }
    }
}