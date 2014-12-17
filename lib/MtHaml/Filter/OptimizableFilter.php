<?php

namespace MtHaml\Filter;

use MtHaml\Node\InterpolatedString;
use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;
use MtHaml\Node\Text;

abstract class OptimizableFilter extends AbstractFilter
{
    private $forceOptimization;

    public function __construct($forceOptimization = false)
    {
        $this->forceOptimization = $forceOptimization;
    }

    public function isOptimizable(Renderer $renderer, Filter $node, $options)
    {
        if ($this->forceOptimization) {
            return true;
        }

        return parent::isOptimizable($renderer, $node, $options);
    }

    public function optimize(Renderer $renderer, Filter $node, $options)
    {
        $inserts = array();
        $content = '';
        foreach ($node->getChilds() as $child) {
            foreach ($child->getContent()->getChilds() as $item) {
                if ($item instanceof Text) {
                    $content .= $item->getContent();
                } else {
                    $hash = md5(mt_rand());
                    $inserts[$hash] = $item;
                    $content .= $hash;
                }
            }
            $content .= "\n";
        }

        $string = new InterpolatedString(array());
        $result = $this->filter($content, array(), array());
        foreach ($inserts as $hash => $insert) {
            $parts = explode($hash, $result, 2);
            $string->addChild(new Text(array(), $parts[0]));
            $string->addChild($insert);
            $result = $parts[1];
        }
        $string->addChild(new Text(array(), $result));
        $string->accept($renderer);
    }

    abstract public function filter($content, array $context, $options);
}
