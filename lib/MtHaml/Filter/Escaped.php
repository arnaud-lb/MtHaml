<?php

namespace MtHaml\Filter;

class Escaped extends Plain {
	
	protected $name = 'escaped';
	
	public function line($buffer, $options)
	{
		$buffer->replaceLine(htmlspecialchars($buffer->getLine()));
		
		return parent::line($buffer, $options);
	}
	
}