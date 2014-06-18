<?php

namespace MtHaml\Node;

use MtHaml\Exception;
use MtHaml\NodeVisitor\NodeVisitorInterface;

abstract class NestAbstract extends NodeAbstract implements NestInterface
{
    private $content;
    private $childs = array();

    public function addChild(NodeAbstract $node)
    {
        if (!$this->allowsNestingAndContent() && $this->hasContent()) {
            throw new Exception('A node cannot have both content and nested nodes');
        }
        if (null !== $parent = $node->getParent()) {
            $parent->removeChild($node);
        }

        $prev = end($this->childs) ?: null;

        $this->childs[] = $node;
        $node->setParent($this);

        if ($prev) {
            $prev->setNextSibling($node);
        }
        $node->setPreviousSibling($prev);
        $node->setNextSibling(null);
    }

    public function removeChild(NodeAbstract $node)
    {
        if (false === $key = array_search($node, $this->childs, true)) {
            return;
        }

        unset($this->childs[$key]);

        $prev = $node->getPreviousSibling();
        $next = $node->getNextSibling();

        if ($prev) {
            $prev->setNextSibling($next);
        }
        if ($next) {
            $next->setPreviousSibling($prev);
        }

        $node->setParent(null);
        $node->setPreviousSibling(null);
        $node->setNextSibling(null);
    }

    public function hasChilds()
    {
        return 0 < count($this->childs);
    }

    public function getChilds()
    {
        return $this->childs;
    }

    public function getFirstChild()
    {
        if (false !== $child = reset($this->childs)) {
            return $child;
        }
    }

    public function getLastChild()
    {
        if (false !== $child = end($this->childs)) {
            return $child;
        }
    }

    public function setContent($content)
    {
        if (!$this->allowsNestingAndContent() && $this->hasChilds()) {
            throw new Exception('A node cannot have both content and nested nodes');
        }
        $this->content = $content;
    }

    public function hasContent()
    {
        return null !== $this->content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function allowsNestingAndContent()
    {
        return false;
    }

    public function visitContent(NodeVisitorInterface $visitor)
    {
        if ($this->hasContent()) {
            $this->getContent()->accept($visitor);
        }
    }

    public function visitChilds(NodeVisitorInterface $visitor)
    {
        foreach ($this->getChilds() as $child) {
            $child->accept($visitor);
        }
    }
}
