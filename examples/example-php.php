<?php
/**
 * This example shows how to integrate MtHaml with PHP templates.
 */

require __DIR__ . "/autoload.php";

$haml = new MtHaml\Environment('php');
$hamlExecutor = new MtHaml\Support\Php\Executor($haml, array(
    'cache' => sys_get_temp_dir().'/haml',
));

/*
 * Execute the template
 */

echo "\n\nExecuted Template:\n\n";

$template = __DIR__ . '/example-php.haml';
$variables = array(
    'foo' => 'bar',
);

try {
    $hamlExecutor->display($template, $variables);
} catch (MtHaml\Exception $e) {
    echo "Failed to execute template: ", $e->getMessage(), "\n";
}

/*
 * See how it was compiled
 */ 
 
echo "\n\nHow the template was compiled:\n\n";

echo $haml->compileString(file_get_contents($template), $template), "\n";

