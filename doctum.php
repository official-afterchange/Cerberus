<?php

use Doctum\Doctum;
use Symfony\Component\Finder\Finder;

require __DIR__ . '/vendor/autoload.php';

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src');

return new Doctum($iterator, [
    'title' => 'Elegance Documentation',
    'language' => 'fr',
    'build_dir' => __DIR__ . '/docs',
    'cache_dir' => __DIR__ . '/cache',
    'source_dir' => __DIR__ . '/src', 
]);