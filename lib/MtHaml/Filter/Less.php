<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;

abstract class Less extends AbstractFilter
{
    public function optimize(Renderer $renderer, Filter $node, $options)
    {
        $renderer->write($this->filter($this->getContent($node), array(), $options));
    }

    public function filter($content, array $context, $options)
    {
        $css = $this->getCss($content, $context, $options);

        if (isset($options['cdata']) && $options['cdata'] === true) {
            return "<style type=\"text/css\">\n/*<![CDATA[*/\n".$css."\n/*]]>*/\n</style>";
        }

        return "<style type=\"text/css\">\n".$css."\n</style>";
    }

    abstract protected function getCss($content, array $context, $options);
}
