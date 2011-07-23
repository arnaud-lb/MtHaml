<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

class Comment extends NestAbstract
{
    protected $rendered;
    protected $condition;

    public function __construct(array $position, $rendered, $condition = null)
    {
        parent::__construct($position);
        $this->rendered = $rendered;
        $this->condition = $condition;
    }

    public function isRendered()
    {
        return $this->rendered;
    }

    public function hasCondition()
    {
        return null !== $this->condition;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function getNodeName()
    {
        return 'comment';
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        if (false !== $visitor->enterComment($this)) {

            if (false !== $visitor->enterCommentContent($this)) {
                $this->visitContent($visitor);
            }
            $visitor->leaveCommentContent($this);

            if (false !== $visitor->enterCommentChilds($this)) {
                $this->visitChilds($visitor);
            }
            $visitor->leaveCommentChilds($this);
        }
        $visitor->leaveComment($this);
    }
}

