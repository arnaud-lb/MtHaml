<?php

namespace MtHaml;

use MtHaml\Node\NestInterface;
use MtHaml\Node\Statement;
use MtHaml\Node\Root;
use MtHaml\Node\NodeAbstract;
use MtHaml\Node\Tag;

class TreeBuilder
{
    /**
     * @var array<\MtHaml\Node\NodeAbstract>
     */
    private $parentStack;

    /**
     * @var \MtHaml\Node\NodeAbstract
     */
    private $parent;

    /**
     * @var \MtHaml\Node\NodeAbstract|null
     */
    private $prev;

    public function __construct()
    {
        $this->parentStack = array();
        $this->parent = new Root();
    }

    public function addChild($level, NodeAbstract $node)
    {
        $this->updateStack($level);

        if (!$this->parent instanceof NestInterface) {
            $parent = $this->parent;
            if ($parent instanceof Statement) {
                $parent = $parent->getContent();
            }
            $msg = sprintf('Illegal nesting: nesting within %s is illegal', $parent->getNodeName());
            throw new TreeBuilderException($msg);
        }

        if ($this->parent->hasContent() && !$this->parent->allowsNestingAndContent()) {
            if ($this->parent instanceof Tag) {
                $msg = sprintf('Illegal nesting: content can\'t be both given on the same line as %%%s and nested within it', $this->parent->getTagName());
            } else {
                $msg = sprintf('Illegal nesting: nesting within a tag that already has content is illegal');
            }
            throw new TreeBuilderException($msg);
        }

        if ($this->parent instanceof Tag && $this->parent->getFlags() & Tag::FLAG_SELF_CLOSE) {
            $msg = 'Illegal nesting: nesting within a self-closing tag is illegal';
            throw new TreeBuilderException($msg);
        }

        $this->parent->addChild($node);
        $this->prev = $node;
    }

    public function getRoot()
    {
        if (count($this->parentStack) > 0) {
            return $this->parentStack[0];
        } else {
            return $this->parent;
        }
    }

    public function hasStatements()
    {
        if (!$this->parent instanceof Root) {
            return true;
        }
        return $this->parent->hasChilds();
    }

    private function updateStack($level)
    {
        // open node

        if ($level > 0) {

            $this->parentStack[] = $this->parent;
            $this->parent = $this->prev;

        // close node(s)

        } elseif ($level < 0) {

            for ($i = $level; $i < 0; ++$i) {
                $this->parent = array_pop($this->parentStack);
            }
        }
    }
}
