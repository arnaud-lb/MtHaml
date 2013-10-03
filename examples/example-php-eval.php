<?php
/**
 * This example shows how to integrate MtHaml with PHP templates.
 * 
 * This uses eval(), which is slower and evil, but simpler.
 * 
 * Prefer the non-eval example: example-php.php.
 */

require __DIR__ . "/autoload.php";

$haml = new MtHaml\Environment('php');

/*
 * Compile the template to PHP
 */

$template = __DIR__ . '/example-php.haml';
$hamlCode = file_get_contents($template);
$phpCode = $haml->compileString($hamlCode, $template);

/*
 * Execute the compiled template
 */

echo "\n\nExecuted Template:\n\n";

extract([
    'foo' => 'bar',
]);

// Note that it's slower than example-php.php because the HAML code is compiled
// everytime. Also, you may not want to use eval().
eval("?>" . $phpCode);

echo "\n\nRendered Template:\n\n";

echo $phpCode;

