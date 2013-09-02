<?php
/**
 * This example shows how to integrate MtHaml with PHP templates.
 */

require __DIR__ . "/autoload.php";

$haml = new MtHaml\Environment('php');

/*
 * Compile the template to PHP
 */

$template = __DIR__ . '/example-php.haml';
$hamlCode = file_get_contents($template);

// no need to compile if already compiled and up to date
if (!file_exists($template.'.php') || filemtime($template.'.php') != filemtime($template)) {

    $phpCode = $haml->compileString($hamlCode, $template);

    $tempnam = tempnam(dirname($template), basename($template));
    file_put_contents($tempnam, $phpCode);
    rename($tempnam, $template.'.php');
    touch($template.'.php', filemtime($template));
}

/*
 * Execute the compiled template
 */

extract([
    'foo' => 'bar',
]);

require $template.'.php';

echo "\nrendered template:\n";

readfile($template.'.php');

