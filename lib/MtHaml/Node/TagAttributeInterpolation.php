<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

class TagAttributeInterpolation extends TagAttribute
{
    public function __construct(array $position, NodeAbstract $value = null)
    {
        parent::__construct($position, null, $value);
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        if (false !== $visitor->enterTagAttribute($this)) {
            if (false !== $visitor->enterTagAttributeInterpolation($this)) {
                $this->getValue()->accept($visitor);
            }
            $visitor->leaveTagAttributeInterpolation($this);
        }
        $visitor->leaveTagAttribute($this);
    }
}
