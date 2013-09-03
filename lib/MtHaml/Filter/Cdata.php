<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;

class Cdata extends FilterAbstract {
	
	protected $name = 'cdata';
	
	public function enter(Renderer $renderer, $options)
	{
		$renderer->write('<![CDATA[')->indent();
	}
	
	public function leave(Renderer $renderer, $options)
	{
		$renderer->undent()->write(']]>');
	}
}