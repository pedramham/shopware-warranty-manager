<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Trait;

/**
 * Since we might use some private methods from parent class on decorating,
 * we need this helper to be able to do that,
 */
trait CallParentPrivateTrait
{
    /**
     * @phpstan-ignore-next-line
     */
    public function callPrivateMethod(object $obj, string $methodName, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
