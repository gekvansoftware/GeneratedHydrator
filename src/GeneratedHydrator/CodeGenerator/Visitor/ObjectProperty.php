<?php

declare(strict_types=1);

namespace GeneratedHydrator\CodeGenerator\Visitor;

use Doctrine\Common\Annotations\AnnotationReader;
use GeneratedHydrator\NestedHydrator;
use GeneratedHydrator\MappedFrom;
use ReflectionProperty;
use function array_key_exists;
use function class_exists;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class ObjectProperty
{
    public bool $hasType;
    public bool $hasDefault;
    public bool $allowsNull;
    /** @psalm-var non-empty-string */
    public string $name;
    public string $mappedFrom;
    public ?string $factory;
    public ?string $target;

    /** @psalm-param non-empty-string $name
     * @param string $name
     * @param bool $hasType
     * @param bool $allowsNull
     * @param bool $hasDefault
     * @param string $mappedFrom
     * @param string|null $factory
     */
    private function __construct(string $name, bool $hasType, bool $allowsNull, bool $hasDefault, string $mappedFrom, ?string $factory, ?string $target)
    {
        $this->name       = $name;
        $this->hasType    = $hasType;
        $this->allowsNull = $allowsNull;
        $this->hasDefault = $hasDefault;
        $this->mappedFrom = $mappedFrom;
        $this->factory    = $factory;
        $this->target     = $target;
    }

    public static function fromReflection(ReflectionProperty $property) : self
    {
        /** @psalm-var non-empty-string $propertyName */
        $propertyName  = $property->getName();
        $type          = $property->getType();
        $defaultValues = $property->getDeclaringClass()->getDefaultProperties();
        $mappedFrom    = $propertyName;
        $factory       = null;

        if (class_exists(AnnotationReader::class) === true) {
            $reader = new AnnotationReader();
            $mappedFromAnnotation = $reader->getPropertyAnnotation($property, MappedFrom::class);
            $factoryAnnotation = $reader->getPropertyAnnotation($property, NestedHydrator::class);
            $mappedFrom = $mappedFromAnnotation->name ?? $propertyName;

            if ($factoryAnnotation !== null
                && (!class_exists($factoryAnnotation->abstractFactory ?? '') || !class_exists($factoryAnnotation->target ?? ''))
            ) {
                throw new \Exception('The class name you provided is not valid.');
            }

            $factory = $factoryAnnotation->abstractFactory ?? $factoryAnnotation;
            $target = $factoryAnnotation->target ?? $factoryAnnotation;
        }

        if ($type === null) {
            return new self($propertyName, false, true, array_key_exists($propertyName, $defaultValues), $mappedFrom, $factory, $target);
        }

        return new self(
            $propertyName,
            true,
            $type->allowsNull(),
            array_key_exists($propertyName, $defaultValues),
            $mappedFrom,
            $factory,
            $target
        );
    }
}
