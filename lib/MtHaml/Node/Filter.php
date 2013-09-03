<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;
use MtHaml\NodeVisitor\RendererAbstract;
use MtHaml\Environment;
use MtHaml\Filter\FilterInterface;
use Exception;

class Filter extends NodeAbstract
{
    private $childs = array();
    private $filter;

    public function __construct(array $position, FilterInterface $filter)
    {
        parent::__construct($position);
        $this->filter = $filter;
    }
    
    public function getFilter()
    {
	    return $this->filter;
    }

    public function addChild(NodeAbstract $node)
    {
        $this->childs[] = $node;
    }

    public function getChilds()
    {
        return $this->childs;
    }

    public function getNodeName()
    {
        return 'filter';
    }
    
    public function accept(NodeVisitorInterface $visitor)
    {
        if (false !== $visitor->enterFilter($this)) {
            
            if (false !== $visitor->enterFilterChilds($this)) {
                foreach($this->getChilds() as $child) {
                    $child->accept($visitor);
                }
            }
            $visitor->leaveFilterChilds($this);
        }
        $visitor->leaveFilter($this);
    }
}

