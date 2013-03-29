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
    private $instance;

    public function __construct(array $position, $filter)
    {
        parent::__construct($position);
        $this->filter = $filter;
    }
    
    /**
     * Get the filter instance from filter name.
     * 
     * @access public
     * @param Environment $env
     * @return FilterInterface
     */
    public function getFilter(RendererAbstract $renderer)
    {
    	if (null === $this->instance) {
	    	$this->loadFilter($renderer);
    	}
    	return $this->instance;
    }
    
    /**
     * Get the filter name.
     * 
     * @access public
     * @return String
     */
    public function getFilterName()
    {
	    return $this->filter;
    }
    
    /**
     * Load a filter from Environment.
     * 
     * @access public
     * @param Environment $env
     * @return void
     */
    public function loadFilter(RendererAbstract $renderer)
    {
    	$env = $renderer->getEnvironment();
    	
    	// Check if the filter name is registered or instanciated
    	if (! $filter = $env->getFilter($this->filter)) {
	    	throw new Exception("unknown filter " . $this->filter);
    	}
        // Try to load a filter instance and store it into env array
        // in order to not instanciate filter each time the filter is requested
        if (is_string($filter)) {
        	if (! class_exists($filter)) {
		        throw new Exception(sprintf('"%s": Filter class doesn\'t exists', $filter));
		    }
	        $filter = new $filter;
	        $env->addFilter($this->filter, $filter);
        }
        // Assign filter to the current instance, and inject renderer if not present
        if ($filter instanceof FilterInterface) {
        	! $filter->hasRenderer() and $filter->setRenderer($renderer);
	        return $this->instance = $filter;
        }
	    throw new Exception(
	    	"Filter should be an instance of MtHaml\Filter\FilterInterface"
	    );
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

