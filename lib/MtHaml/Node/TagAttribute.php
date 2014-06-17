<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

class TagAttribute extends NodeAbstract
{
    protected $name;
    protected $value;

    public function __construct(array $position, NodeAbstract $name = null, NodeAbstract $value = null)
    {
        parent::__construct($position);
        $this->name = $name;
        $this->value = $value;
    }

    public function setName(NodeAbstract $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue(NodeAbstract $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getNodeName()
    {
        return 'attribute';
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        if (false !== $visitor->enterTagAttribute($this)) {

            if (false !== $visitor->enterTagAttributeName($this)) {
                $this->getName()->accept($visitor);
            }
            $visitor->leaveTagAttributeName($this);

            if ($this->getValue()) {
                if (false !== $visitor->enterTagAttributeValue($this)) {
                    $this->getValue()->accept($visitor);
                }
                $visitor->leaveTagAttributeValue($this);
            }
        }
        $visitor->leaveTagAttribute($this);
    }
}
