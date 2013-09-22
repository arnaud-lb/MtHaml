<?php

namespace MtHaml\Filter;

use MtHaml\Node\Filter;
use MtHaml\NodeVisitor\RendererAbstract;

interface FilterInterface
{
    public function isOptimizable(RendererAbstract $renderer, Filter $node, $options);

    public function optimize(RendererAbstract $renderer, Filter $node, $options);

    public function filter($content, array $context, $options);
}
