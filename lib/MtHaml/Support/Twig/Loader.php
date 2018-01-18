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
        $code = $this->loader->getSource($name);

        $code->renderHaml($name, $code);

        return $code;
    }

    /**
     * Supports Twig 2.x
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        $source = $this->loader->getSourceContext($name);

        $code = $source->getCode();
        $code = $this->renderHaml($name, $code);

        $source = new \Twig_Source($code, $source->getName(), $source->getPath());

        return $source;
    }

    protected function renderHaml($name, $code)
    {
        if ('haml' === pathinfo($name, PATHINFO_EXTENSION)) {
            $code = $this->env->compileString($code, $name);
        } elseif (preg_match('#^\s*{%\s*haml\s*%}#', $code, $match)) {
            $padding = str_repeat(' ', strlen($match[0]));
            $code = $padding . substr($code, strlen($match[0]));
            $code = $this->env->compileString($code, $name);
        }

        return $code;
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
        }

        // for Twig 2.x
        if ($this->loader instanceof \Twig_SourceContextLoaderInterface) {
            try {
                $this->loader->getSourceContext($name);

                return true;
            } catch (\Twig_Error_Loader $e) {
                return false;
            }
        }

        // for Twig 1.x
        try {
            $this->loader->getSource($name);

            return true;
        } catch (\Twig_Error_Loader $e) {
            return false;
        }
    }
}
