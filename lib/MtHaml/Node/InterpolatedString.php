<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

class InterpolatedString extends NodeAbstract
{
    protected $childs;

    public function __construct(array $position, array $childs = array())
    {
        parent::__construct($position);
        $this->childs = $childs;
    }

    public function addChild(NodeAbstract $child)
    {
        $this->childs[] = $child;
    }

    public function getChilds()
    {
        return $this->childs;
    }

    public function getNodeName()
    {
        return 'interpolated string';
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        if (false !== $visitor->enterInterpolatedString($this)) {

            if (false !== $visitor->enterInterpolatedStringChilds($this)) {
                foreach($this->getChilds() as $child) {
                    $child->accept($visitor);
                }
                $visitor->leaveInterpolatedStringChilds($this);
            }
            $visitor->leaveInterpolatedString($this);
        }
    }
}

