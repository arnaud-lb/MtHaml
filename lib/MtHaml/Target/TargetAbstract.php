<?php

namespace MtHaml\Target;

use MtHaml\Environment;
use MtHaml\Node\NodeAbstract;
use MtHaml\Parser;

abstract class TargetAbstract implements TargetInterface
{
    protected $options = array();
    protected $parserFactory;
    protected $rendererFactory;

    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    public function getDefaultParserFactory()
    {
        return function (Environment $env, array $options) {
            return new Parser;
        };
    }

    public function getParserFactory()
    {
        if (null === $this->parserFactory) {
            $this->parserFactory = $this->getDefaultParserFactory();
        }

        return $this->parserFactory;
    }

    public function setParserFactory($factory)
    {
        $this->parserFactory = $factory;
    }

    public function createParser(Environment $env, array $options)
    {
        return call_user_func($this->getParserFactory(), $env, $options);
    }

    abstract public function getDefaultRendererFactory();

    public function getRendererFactory()
    {
        if (null === $this->rendererFactory) {
            $this->rendererFactory = $this->getDefaultRendererFactory();
        }

        return $this->rendererFactory;
    }

    public function setRendererFactory($factory)
    {
        $this->rendererFactory = $factory;
    }

    public function createRenderer(Environment $env, array $options)
    {
        return call_user_func($this->getRendererFactory(), $env, $options);
    }

    public function parse(Environment $env, $string, $filename)
    {
        $parser = $this->createParser($env, $this->options);

        return $parser->parse($string, $filename);
    }

    public function compile(Environment $env, NodeAbstract $node)
    {
        $renderer = $this->createRenderer($env, array());

        $node->accept($renderer);

        return $renderer->getOutput();
    }

    public function getOption($name)
    {
        return $this->options[$name];
    }
}
