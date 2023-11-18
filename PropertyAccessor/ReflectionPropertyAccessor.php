<?php

declare(strict_types=1);

namespace Dev\ViewBundle\PropertyAccessor;

use Doctrine\Persistence\Proxy;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class ReflectionPropertyAccessor implements PropertyAccessorInterface
{
    public function __construct(
        private readonly PropertyAccessorInterface $decorated,
        private readonly ReflectionService $reflectionService
    )
    {
    }

    public function setValue(object|iterable &$objectOrArray, string|PropertyPathInterface $propertyPath, mixed $value): void
    {
        if ($objectOrArray instanceof Proxy && !$objectOrArray->__isInitialized()) {
            $objectOrArray->__load();
        }

        // only public properties of view are supported
        $this->decorated->setValue($objectOrArray, $propertyPath, $value);
    }

    /**
     * @throws \ReflectionException
     */
    public function getValue(object|iterable $objectOrArray, string|PropertyPathInterface $propertyPath): mixed
    {
        if ($objectOrArray instanceof Proxy && !$objectOrArray->__isInitialized()) {
            $objectOrArray->__load();
        }

        try {
            return $this->decorated->getValue($objectOrArray, $propertyPath);
        } catch (NoSuchPropertyException|\Error $e) {
            if (
                !$e instanceof NoSuchPropertyException
                && !\preg_match('/^Cannot access (private|protected) property '.\preg_quote(\get_debug_type($objectOrArray), '/').'::\$'.$propertyPath.'$/', $e->getMessage(), $matches)) {
                throw $e;
            }

            if (null === $property = $this->getReflectionProperty($objectOrArray, $propertyPath)) {
                throw $e;
            }

            return $property->getValue($objectOrArray);
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function isWritable(object|iterable $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        return $this->decorated->isWritable($objectOrArray, $propertyPath) || $this->propertyExists($objectOrArray, $propertyPath);
    }

    /**
     * @throws \ReflectionException
     */
    public function isReadable(object|iterable $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        return $this->decorated->isReadable($objectOrArray, $propertyPath) || $this->propertyExists($objectOrArray, $propertyPath);
    }

    public function isStrictlyReadable(object|iterable $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        return $this->decorated->isReadable($objectOrArray, $propertyPath);
    }

    /**
     * @throws \ReflectionException
     */
    private function propertyExists(object|iterable $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        return null !== $this->getReflectionProperty($objectOrArray, (string) $propertyPath);
    }

    /**
     * @throws \ReflectionException
     */
    private function getReflectionProperty(object|iterable $objectOrArray, string $propertyPath): ?\ReflectionProperty
    {
        if (false === \is_object($objectOrArray)) {
            return null;
        }

        return $this->reflectionService->getReflectionProperty($objectOrArray, $propertyPath);
    }
}
