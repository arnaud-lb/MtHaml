<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract;

interface FilterInterface {
	
	public function enter(RendererAbstract $renderer, $options);
	
	public function leave(RendererAbstract $renderer, $options);
	
	public function line($line, $options);
	
	public function getName();
	
}