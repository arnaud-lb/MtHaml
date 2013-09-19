<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;
use CoffeeScript\Compiler;

class CoffeeScript extends AbstractFilter
{
    private $coffeescript;
    private $options;

    public function __construct(Compiler $coffeescript, array $options = array())
    {
        $this->coffeescript = $coffeescript;
        $this->options = $options;
    }

    public function optimize(Renderer $renderer, Filter $node, $options)
    {
        $renderer->write($this->filter($this->getContent($node), array(), $options));
    }

    public function filter($content, array $context, $options)
    {
        if (isset($options['cdata']) && $options['cdata'] === true) {
            return "<script type=\"text/javascript\">\n//<![CDATA[\n".$this->coffeescript->compile($content, $this->options)."\n//]]\n</script>";
        }

        return "<script type=\"text/javascript\">\n".$this->coffeescript->compile($content, $this->options)."\n</script>";
    }
}
