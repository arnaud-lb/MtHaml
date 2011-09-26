<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

/**
 * Insert Node
 *
 * Represents code to execute and whose result is inserted in the document.
 */
class Insert extends EscapableAbstract
{
    protected $content;

    public function __construct(array $position, $content)
    {
        parent::__construct($position);
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function hasContent()
    {
        return null !== $this->content;
    }

    public function getNodeName()
    {
        return 'echo';
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        $visitor->enterInsert($this);
        $visitor->leaveInsert($this);
    }
}

