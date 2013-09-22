<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;

class Cdata extends Plain
{
    public function optimize(Renderer $renderer, Filter $node, $options)
    {
        $renderer->write('<![CDATA[')->indent();
        $this->renderFilter($renderer, $node);
        $renderer->undent()->write(']]>');
    }
}
