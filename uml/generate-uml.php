<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$baseDir = realpath(__DIR__ . '/../src/Models');
$outputFile = __DIR__ . '/diagram.puml';

$classes = [];

function getPhpClasses(string $dir): array
{
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $classes = [];

    foreach ($files as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $before = get_declared_classes();

        require_once $file->getPathname();

        $after = get_declared_classes();
        $newClasses = array_diff($after, $before);

        foreach ($newClasses as $class) {
            $reflection = new ReflectionClass($class);
            $filename = $reflection->getFileName();

            if ($filename !== false && str_starts_with(realpath($filename), $dir)) {
                $classes[$class] = $reflection;
            }
        }
    }

    return $classes;
}

$classes = getPhpClasses($baseDir);

$puml = "@startuml\n\n";

foreach ($classes as $reflection) {
    $puml .= "class " . $reflection->getShortName() . " {\n";

    foreach ($reflection->getProperties() as $prop) {
        $visibility = $prop->isPublic() ? '+' : ($prop->isProtected() ? '#' : '-');
        $type = $prop->hasType() ? (string)$prop->getType() : '';
        $puml .= "  {$visibility}{$type} {$prop->getName()}\n";
    }

    foreach ($reflection->getMethods() as $method) {
        if ($method->isConstructor() || $method->isPublic() || $method->isProtected()) {
            $visibility = $method->isPublic() ? '+' : '#';

            $params = [];
            foreach ($method->getParameters() as $param) {
                $ptype = $param->hasType() ? (string)$param->getType() : '';
                $params[] = ($ptype ? $ptype . ' ' : '') . '$' . $param->getName();
            }

            $paramList = implode(', ', $params);
            $puml .= "  {$visibility}{$method->getName()}({$paramList})\n";
        }
    }

    $puml .= "}\n\n";

    if ($parent = $reflection->getParentClass()) {
        $puml .= $reflection->getShortName() . " --> " . $parent->getShortName() . "\n\n";
    }
}

$puml .= "@enduml\n";

file_put_contents($outputFile, $puml);

echo "diagram.puml généré avec succès !\n";
