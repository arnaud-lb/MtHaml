<?php

namespace MtHaml\Filter;

use ArrayAccess;
use Iterator;
use Exception;

class FilterProvider implements ArrayAccess, Iterator {
	
	protected $filters = array(
		'css' => 'MtHaml\\Filter\\Css',
		'cdata' => 'MtHaml\\Filter\\Cdata',
		'escaped' => 'MtHaml\\Filter\\Escaped',
	    'javascript' => 'MtHaml\\Filter\\Javascript',
		'php' => 'MtHaml\\Filter\\Php',
		'plain' => 'MtHaml\\Filter\\Plain',
		'preserve' => 'MtHaml\\Filter\\Preserve',
	);
	
	public function __construct(array $filters)
	{
		$this->filters = $filters + $this->filters;
	}
	    
	public function get($key)
	{
		if (! isset($this->filters[$key])) {
			throw new Exception("unknown filter " . $key);
		}
		
		$filter = $this->filters[$key];
		
        if (is_string($filter)) {
        	if (! class_exists($filter)) {
		        throw new Exception(sprintf('"%s": Filter class doesn\'t exists', $filter));
		    }
	        $filter = $this->set(new $filter);
        }

        if (! $filter instanceof FilterInterface) {
		    throw new Exception(
		    	'Filter should be an instance of FilterInterface'
		    );
        }
        
        return $filter;
	}
	
	public function set($key, $val = null)
	{
	    if ($key instanceof FilterInterface) {
	    	$obj = $key;
	    	$key = $val ?: $obj->getName();
	    	$val = $obj;
    	}
		
		return $this->filters[$key] = $val;
	}
	
	public function offsetExists($key)
	{
		return isset($this->filters[$key]);
	}
	
	public function offsetGet($key)
	{
		return $this->get($key);
	}
	
	public function offsetSet($key, $val = null)
	{
		return $this->set($key, $val);
	}
	
	public function offsetUnset($key)
	{
		if (array_key_exists($key, $this->filters)) {
			unset($this->filters[$key]);
			return true;
		}
		return false;
	}

    public function rewind()
    {
	    reset($this->filters);
    }
	
	public function current()
	{
        return $this->get(key($this->filters));
    }
    
    public function key()
    {
	    return key($this->filters);
    }
    
    public function next()
    {
	    next($this->filters);
    }
    
    public function valid()
    {
	    return isset($this->filters[key($this->filters)]);
    }

	
}