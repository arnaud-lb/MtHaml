<?php

namespace MtHaml\Filter;

use MtHaml\Node\InterpolatedString;
use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;
use MtHaml\Node\Text;

class Markdown extends AbstractFilter
{
    private $markdown;
    private $forceOptimization;

    public function __construct($markdown, $forceOptimization = false)
    {
        if (!is_object($markdown) || (!is_a($markdown, 'Michelf\Markdown') && !is_a($markdown, 'Parsedown') && !is_a($markdown, 'cebe\markdown\Parser') && !is_a($markdown, 'Ciconia\Ciconia'))) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s::__construct() must be an instance of %s or %s or %s or %s, %s given',
                __CLASS__, 'Michelf\Markdown', 'Parsedown', 'cebe\markdown\Parser', 'Ciconia\Ciconia', is_object($markdown) ? 'instance of '.get_class($markdown) : gettype($markdown)
            ));
        }

        $this->markdown = $markdown;
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

    public function filter($content, array $context, $options)
    {
        if (is_a($this->markdown, 'Parsedown')) {
            return $this->markdown->text($content);
        } elseif (is_a($this->markdown, 'cebe\markdown\Parser')) {
            return $this->markdown->parse($content);
        } elseif (is_a($this->markdown, 'Ciconia\Ciconia')) {
            return $this->markdown->render($content);
        } else {
            return $this->markdown->transform($content);
        }
    }
}
