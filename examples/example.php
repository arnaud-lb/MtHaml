<?php

use MtHaml\Autoloader;
use MtHaml\Environment;

require_once __DIR__ . '/../lib/MtHaml/Autoloader.php';

Autoloader::register();

$haml = new Environment('twig');

$template = __DIR__ . '/example.twig.haml';
$compiled = $haml->compileString(file_get_contents($template), $template);

echo "rendered template:\n";

echo $compiled;

