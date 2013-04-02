<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;

class Php extends Plain {
	
	protected $name = 'php';
	
	public function enter(Renderer $renderer, $options)
	{
		$renderer->write('<?php')->indent();
	}
		
	public function leave(Renderer $renderer, $options)
	{
		$renderer->write(' ?>'.PHP_EOL)->undent();
	}
	
}