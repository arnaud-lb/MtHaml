<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\NodeVisitor\PhpRenderer;
use MtHaml\Node\Filter;

class Php extends AbstractFilter
{
    public function isOptimizable(Renderer $renderer, Filter $node, $options)
    {
        if (!$renderer instanceof PhpRenderer) {
            return false;
        }

        return parent::isOptimizable($renderer, $node, $options);
    }

    public function optimize(Renderer $renderer, Filter $node, $options)
    {
        $renderer->write('<?php')->indent();
        $this->renderFilter($renderer, $node);
        $renderer->undent()->write('?>');
    }

    public function filter($content, array $context, $options)
    {
        $__content__ = '?><?php '.$content;
        unset($options, $content);
        extract($context);
        ob_start();
        eval($__content__);

        return ob_get_clean();
    }
}
