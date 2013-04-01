<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;

class Css extends FilterAbstract {
	
	protected $name = 'css';
	
	public function enter(Renderer $renderer, $options)
	{
		$renderer->write('<style type="text/css">');
		if ($options['cdata'] === true) {
			$renderer->write('/*<![CDATA[*/');
		}
		$renderer->indent();
	}
	
	public function leave(Renderer $renderer, $options)
	{
		$renderer->undent();
		if ($options['cdata'] === true) {
			$renderer->write('/*]]>*/');
		}
		$renderer->write('</style>');
	}
}