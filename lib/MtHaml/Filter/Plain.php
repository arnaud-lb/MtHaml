<?php

namespace MtHaml\Filter;

use MtHaml\Node\Text;

class Plain extends FilterAbstract {
	
	protected $name = 'plain';
		
	public function line($buffer, $options)
	{
		return new Text($buffer->getPosition(), $buffer->getLine());
	}
}