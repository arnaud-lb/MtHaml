<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

class Comment extends NestAbstract
{
    protected $rendered;

    public function __construct(array $position, $rendered)
    {
        parent::__construct($position);
        $this->rendered = $rendered;
    }

    public function isRendered()
    {
        return $this->rendered;
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

