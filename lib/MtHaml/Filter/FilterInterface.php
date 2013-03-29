<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract;

interface FilterInterface {
	
	public function setRenderer(RendererAbstract $renderer);
	
	public function hasRenderer();
	
	public function enter();
	
	public function leave();
	
	public function getName();
	
}