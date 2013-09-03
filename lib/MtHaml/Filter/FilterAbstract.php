<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;

abstract class FilterAbstract implements FilterInterface {
	
	protected $name;
	
	public function enter(Renderer $renderer, $options)
	{
		$renderer->indent();
	}
	
	public function leave(Renderer $renderer, $options)
	{
		$renderer->undent();
	}

	public function line($buffer, $options)
	{
	}

	public function getName()
	{
		return $this->name;
	}
	
}