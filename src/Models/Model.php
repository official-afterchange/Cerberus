<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

abstract class Model
{
    public ?int $id;

    public function hydrate(array $data): void
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

    public function getFillable(): array
    {
        $ref = new \ReflectionClass($this);
        $props = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);

        return \array_map(fn($p) => $p->getName(), $props);
    }
}
