<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;

class Preserve extends Plain
{
    public function optimize(Renderer $renderer, Filter $filter, $options)
    {
        $renderer->pushSavedIndent($renderer->getIndent());
        $renderer->setIndent(0);

        $this->renderFilter($renderer, $filter);

        $renderer->setIndent($renderer->popSavedIndent());
    }
}
