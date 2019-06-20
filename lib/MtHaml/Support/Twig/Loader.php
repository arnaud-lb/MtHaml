<?php

namespace MtHaml\Support\Twig;

use MtHaml\Environment;

/**
 * Example integration of MtHaml with Twig, by proxying the Loader
 *
 * This loader will parse Twig templates as HAML if their filename end with
 * `.haml`, or if the code starts with `{% haml %}`.
 *
 * Alternatively, use MtHaml\Support\Twig\Lexer.
 *
 * <code>
 * $origLoader = $twig->getLoader();
 * $twig->setLoader($mthaml, new \MtHaml\Support\Twig\Loader($origLoader));
 * </code>
 */
class Loader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface, \Twig_SourceContextLoaderInterface
{
    protected $env;
    protected $loader;

    public function __construct(Environment $env, \Twig_LoaderInterface $loader)
    {
        $this->env = $env;
        $this->loader = $loader;
    }

    /**
     * Deprecated in Twig 1.27
     * Removed in Twig 2.x
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        $source = $this->loader->getSource($name);
        if ('haml' === pathinfo($name, PATHINFO_EXTENSION)) {
            $source = $this->env->compileString($source, $name);
        } elseif (preg_match('#^\s*{%\s*haml\s*%}#', $source, $match)) {
            $padding = str_repeat(' ', strlen($match[0]));
            $source = $padding . substr($source, strlen($match[0]));
            $source = $this->env->compileString($source, $name);
        }

        return $source;
    }

    /**
     * Support Twig 2.x
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        $context = $this->loader->getSourceContext($name);
        $source = $context->getCode();

        if ('haml' === pathinfo($name, PATHINFO_EXTENSION)) {
            $source = $this->env->compileString($source, $name);
        } elseif (preg_match('#^\s*{%\s*haml\s*%}#', $source, $match)) {
            $padding = str_repeat(' ', strlen($match[0]));
            $source = $padding . substr($source, strlen($match[0]));
            $source = $this->env->compileString($source, $name);
        }

        return new \Twig\Source($source, $context->getName(), $context->getPath());
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        return $this->loader->getCacheKey($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return $this->loader->isFresh($name, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        if ($this->loader instanceof \Twig_ExistsLoaderInterface) {
            return $this->loader->exists($name);
        } else {
            try {
                $this->loader->getSource($name);

                return true;
            } catch (\Twig_Error_Loader $e) {
                return false;
            }
        }
    }
}
