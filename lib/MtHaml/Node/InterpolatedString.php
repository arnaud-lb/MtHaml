<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

/**
 * InterpolatedString Node
 *
 * Represents a ruby-like interpolated string. Children are Text and Insert
 * nodes.
 */
class InterpolatedString extends NodeAbstract
{
    protected $childs;

    public function __construct(array $position, array $childs = array())
    {
        parent::__construct($position);
        $this->childs = $childs;
    }

    /**
     * @param Text|Insert $child Child
     */
    public function addChild(NodeAbstract $child)
    {

        if (!$child instanceof Text && !$child instanceof Insert) {
            throw new \InvalidArgumentException(sprintf('Argument 1 passed to %s() must be an instance of MtHaml\Node\Text or MtHaml\Node\Insert, instance of %s given', __METHOD__, get_class($child)));
        }

        $this->childs[] = $child;
    }

    /**
     * @return Text|Insert
     */
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

    public function isConst()
    {
        foreach ($this->childs as $child) {
            if (!$child->isConst()) {
                return false;
            }
        }

        return true;
    }
}

