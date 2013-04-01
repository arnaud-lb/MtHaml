<?php

namespace MtHaml\Filter;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Exception;

class FilterProvider implements ArrayAccess, IteratorAggregate {
	
	protected $filters = array(
		'css' => 'MtHaml\\Filter\\Css',
		'cdata' => 'MtHaml\\Filter\\Cdata',
		'escaped' => 'MtHaml\\Filter\\Escaped',
	    'javascript' => 'MtHaml\\Filter\\Javascript',
		'php' => 'MtHaml\\Filter\\Php',
		'plain' => 'MtHaml\\Filter\\Plain',
		'preserve' => 'MtHaml\\Filter\\Preserve',
	);
	
	/**
	 * @access public
	 * @param array $filters (default: array())
	 * @return void
	 */
	public function __construct(array $filters)
	{
		$this->filters = $filters + $this->filters;
	}
	
	/**
	 * Get a filter.
	 * if the filter is not instanciated, we will try to load it.
	 * 
	 * @access public
	 * @param mixed $key
	 * @return FilterInterface or Exception
	 */
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
	
	/**
	 * Set a filter.
	 * 
	 * @access public
	 * @param mixed $key
	 * @param mixed $val
	 * @return offsetSet() result
	 */
	public function set($key, $val = null)
	{
	    if ($key instanceof FilterInterface) {
	    	$obj = $key;
	    	$key = $val ?: $obj->getName();
	    	$val = $obj;
    	}
		
		return $this->filters[$key] = $val;
	}
	
	/**
	 * @access public
	 * @return void
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->filters);
	}
	
	/**
	 * @access public
	 * @param mixed $key
	 * @return Boolean
	 */
	public function offsetExists($key)
	{
		return isset($key, $this->filters);
	}
	
	/**
	 * offsetGet function.
	 * 
	 * @access public
	 * @param mixed $key
	 * @return get() result or false
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}
	
	/**
	 * @access public
	 * @param mixed $key
	 * @param mixed $val
	 */
	public function offsetSet($key, $val = null)
	{
		return $this->set($key, $val);
	}
	
	/**
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		if (array_key_exists($key, $this->filters)) {
			unset($this->filters[$key]);
			return true;
		}
		return false;
	}
	
}