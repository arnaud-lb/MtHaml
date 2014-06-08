<?php
/**
 * This example shows how to integrate MtHaml with Twig by
 * proxying the Twig Loader.
 *
 * Template files with a `.haml` extension, or whose code starts
 * with `{% haml %}` are parsed as HAML.
 */

require __DIR__ . "/autoload.php";

$haml = new MtHaml\Environment('twig', array(
    'enable_escaper' => false, // twig does that already
));

$arrayLoader = new Twig_Loader_Filesystem(array(
    __DIR__,
));

/*
 * Use a custom loader as a proxy to the actual loader. The custom loader is
 * responsible of converting HAML templates to Twig, before returning them.
 */

$hamlLoader = new MtHaml\Support\Twig\Loader($haml, $arrayLoader);

$twig = new Twig_Environment($hamlLoader);

/*
 * Register the Twig extension. Compiled templates sometimes need this to
 * execute, depending on HAML features in use (some filters, some attributes).
 */

$twig->addExtension(new MtHaml\Support\Twig\Extension($haml));

/*
 * Execute the template:
 */

echo "\n\nExecuted Template:\n\n";

if (true) {
    // parsed as haml because of extension
    $twig->display('example-twig.haml', array());
} else {
    // parsed as haml because code starts with {% haml %}
    $twig->display('example-twig-noext.twig', array());
}

/*
 * See how MtHaml compiles HAML to Twig:
 */

$template = __DIR__ . '/example-twig.haml';
$compiled = $haml->compileString(file_get_contents($template), $template);

echo "\n\nHow the template was compiled:\n\n";

echo $compiled;

