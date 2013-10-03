<?php
/**
 * This example shows how to integrate MtHaml with Twig by
 * proxying the Twig Lexer.
 *
 * Template files with a `.haml` extension, or whose code starts
 * with `{% haml %}` are parsed as HAML.
 */

require __DIR__ . "/autoload.php";

$haml = new MtHaml\Environment('twig', array(
    'enable_escaper' => false, // twig does that already
));

$twig = new Twig_Environment(new Twig_Loader_Filesystem(array(
    __DIR__,
)));

/*
 * Register the Twig extension. Compiled templates sometimes need this to
 * execute, depending on HAML features in use (some filters, some attributes).
 */

$twig->addExtension(new MtHaml\Support\Twig\Extension($haml));

/*
 * Set our customer Lexer. This lexer is reponsible of passing the template
 * through MtHaml before passing to Twig.
 */

$lexer = new MtHaml\Support\Twig\Lexer($haml);
$lexer->setLexer($twig->getLexer());
$twig->setLexer($lexer);

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

echo "\n\nRendered Template:\n\n";

echo $compiled;

