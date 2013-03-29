<?php

namespace MtHaml\Filter;

class Preserve extends FilterAbstract {
	
	public function enter()
	{
		$this->renderer
			->addSavedIndent($this->renderer->getIndent())
			->setIndent(0);
	}
	
	public function leave()
	{
		$indent = $this->renderer->getSavedIndent();
		$this->renderer->setIndent(array_pop($indent));
	}
}