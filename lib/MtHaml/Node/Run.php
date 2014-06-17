<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

/**
 * Run Node
 *
 * Represents code to execute. If there is children, the node should be
 * rendered as a block (the renderer should emit a properly closed block).
 */
class Run extends NestAbstract
{
    private $midblock;

    public function __construct(array $position, $content)
    {
        parent::__construct($position);
        $this->setContent($content);
    }

    public function allowsNestingAndContent()
    {
        return true;
    }

    public function getNodeName()
    {
        return 'exec';
    }

    public function setMidblock(Run $midblock = null)
    {
        $this->midblock = $midblock;
    }

    public function getMidblock()
    {
        return $this->midblock;
    }

    public function hasMidblock()
    {
        return null !== $this->midblock;
    }

    public function isBlock()
    {
        return $this->hasChilds() || $this->hasMidblock();
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        if (false !== $visitor->enterRun($this)) {

            if (false !== $visitor->enterRunChilds($this)) {
                $this->visitChilds($visitor);
            }
            $visitor->leaveRunChilds($this);

            if (false !== $visitor->enterRunMidblock($this)) {
                if (null !== $block = $this->getMidblock()) {
                    $block->accept($visitor);
                }
            }
            $visitor->leaveRunMidblock($this);
        }
        $visitor->leaveRun($this);
    }
}
