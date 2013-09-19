<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;

class Plain extends AbstractFilter
{
    public function isOptimizable(Renderer $renderer, Filter $node, $options)
    {
        return true;
    }

    public function optimize(Renderer $renderer, Filter $filter, $options)
    {
        $this->renderFilter($renderer, $filter);
    }

    public function filter($content, array $context, $options)
    {
        throw new \RuntimeException('Filter is optimizable and does not run in runtime');
    }
}
