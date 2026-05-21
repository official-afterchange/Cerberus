<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

/**
 * Base model providing reflection-based hydration and fillable property introspection.
 */
abstract class Model
{
    public ?int $id;

    /**
     * Populates the model's public properties from an associative array, casting DateTime strings automatically.
     *
     * @param array $data Key-value pairs matching the model's public property names.
     * @return void
     */
    final public function hydrate(array $data): void
    {
        $ref = new \ReflectionClass($this);

        foreach ($data as $key => $value) {
            if (!$ref->hasProperty($key)) {
                continue;
            }

            $prop = $ref->getProperty($key);
            $type = $prop->getType()?->getName();

            if ($type === 'DateTime' && !($value instanceof \DateTime)) {
                $value = new \DateTime($value);
            }

            $prop->setValue($this, $value);
        }
    }

    /**
     * Returns the names of all public properties defined on the model.
     *
     * @return array<string> The list of fillable property names.
     */
    final public function getFillable(): array
    {
        $ref = new \ReflectionClass($this);
        $props = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);

        return \array_map(fn ($p) => $p->getName(), $props);
    }
}