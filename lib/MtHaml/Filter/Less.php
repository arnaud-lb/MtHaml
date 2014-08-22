<?php

namespace MtHaml\Filter;

use MtHaml\Filter\Less\AdapterInterface;
use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;

class Less extends AbstractFilter
{
    private $less;

    public function __construct(\lessc $less)
    {
        $this->less = $less;
    }

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

    protected function getCss($content, array $context, $options)
    {
        return $this->less->compile($content);
    }
}
