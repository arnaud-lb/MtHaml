<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;

class Preserve extends FilterAbstract {
	
	protected $name = 'preserve';
	
	public function enter(Renderer $renderer, $options)
	{
		$renderer
			->addSavedIndent($renderer->getIndent())
			->setIndent(0);
	}
	
	public function leave(Renderer $renderer, $options)
	{
		$indent = $renderer->getSavedIndent();
		$renderer->setIndent(array_pop($indent));
	}
}