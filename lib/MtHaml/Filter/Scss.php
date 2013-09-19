<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;

class Scss extends AbstractFilter
{
    private $scss;

    public function __construct(\scssc $scss)
    {
        $this->scss = $scss;
    }

    public function optimize(Renderer $renderer, Filter $node, $options)
    {
        $renderer->write($this->filter($this->getContent($node), array(), $options));
    }

    public function filter($content, array $context, $options)
    {
        if (isset($options['cdata']) && $options['cdata'] === true) {
            return "<style type=\"text/css\">\n/*<![CDATA[*/\n".$this->scss->compile($content)."\n/*]]>*/\n</style>";
        }

        return "<style type=\"text/css\">\n".$this->less->compile($content)."\n</style>";
    }
}
