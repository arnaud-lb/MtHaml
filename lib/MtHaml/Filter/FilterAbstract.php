<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract;

abstract class FilterAbstract implements FilterInterface {
	
	protected $renderer;
	
	protected $name;
	
	public function __construct(RendererAbstract $renderer = null)
	{
		$renderer and $this->setRenderer($renderer);
	}
	
	public function setRenderer(RendererAbstract $renderer)
	{
		$this->renderer = $renderer;
		return $this;
	}
	
	public function hasRenderer()
	{
		return !! $this->renderer;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
}