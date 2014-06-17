<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

class ObjectRefId extends NodeAbstract
{
    protected $object;
    protected $prefix;

    public function __construct($position, NodeAbstract $object, NodeAbstract $prefix = null)
    {
        parent::__construct($position);
        $this->object = $object;
        $this->prefix = $prefix;
    }

    public function getNodeName()
    {
        return 'object_ref_id';
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        if (false !== $visitor->enterObjectRefId($this)) {

            if (false !== $visitor->enterObjectRefObject($this)) {
                $this->object->accept($visitor);
            }
            $visitor->leaveObjectRefObject($this);

            if ($this->prefix) {
                if (false !== $visitor->enterObjectRefPrefix($this)) {
                    $this->prefix->accept($visitor);
                }
                $visitor->leaveObjectRefPrefix($this);
            }
        }
        $visitor->leaveObjectRefId($this);
    }
}
