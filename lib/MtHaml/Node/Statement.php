<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

class Statement extends NodeAbstract
{
    protected $content;

    public function __construct(array $position, NodeAbstract $content)
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
        return 'statement';
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        if (false !== $visitor->enterStatement($this)) {

            if (false !== $visitor->enterStatementContent($this)) {
                if ($this->hasContent()) {
                    $this->getContent()->accept($visitor);
                }
            }
            $visitor->leaveStatementContent($this);
        }
        $visitor->leaveStatement($this);
    }
}
