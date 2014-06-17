<?php

namespace MtHaml\Support\Twig;

use MtHaml\Environment;

/**
 * Example integration of MtHaml with Twig, by proxying the Lexer
 *
 * This lexer will parse Twig templates as HAML if their filename end with
 * `.haml`, or if the code starts with `{% haml %}`.
 *
 * Alternatively, use MtHaml\Support\Twig\Loader.
 *
 * <code>
 * $lexer = new \MtHaml\Support\Twig\Lexer($mthaml);
 * $lexer->setLexer($twig->getLexer());
 * $twig->setLexer($lexer);
 * </code>
 */
class Lexer implements \Twig_LexerInterface
{
    protected $env;
    protected $lexer;

    public function __construct(Environment $env)
    {
        $this->env = $env;
    }

    public function setLexer(\Twig_LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    public function tokenize($code, $filename = null)
    {
        if (preg_match('#^\s*{%\s*haml\s*%}#', $code, $match)) {
            $padding = str_repeat(' ', strlen($match[0]));
            $code = $padding . substr($code, strlen($match[0]));
            $code = $this->env->compileString($code, $filename);
        } elseif (null !== $filename && 'haml' === pathinfo($filename, PATHINFO_EXTENSION)) {
            $code = $this->env->compileString($code, $filename);
        }

        return $this->lexer->tokenize($code, $filename);
    }
}
