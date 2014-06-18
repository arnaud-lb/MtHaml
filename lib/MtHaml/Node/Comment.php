<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

/**
 * Comment Node
 */
class Comment extends NestAbstract
{
    protected $rendered;
    protected $condition;

    /**
     * @param array  $position
     * @param bool   $rendered  Whether the comment is rendered in the
     *                          HTML output (as a HTML comment).
     * @param string $condition IE condition. If not null, the HTML comment
     *                          will be rendered as an IE conditional
     *                          comment.
     */
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

    public function allowsNestingAndContent()
    {
        return ! $this->rendered;
    }
}
