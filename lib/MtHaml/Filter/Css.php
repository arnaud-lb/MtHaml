<?php

namespace MtHaml\Filter;

class Css extends FilterAbstract {
	
	public function enter()
	{
		$this->renderer->write('<style type="text/css">')
			->write('/*<![CDATA[*/')
			->indent();
	}
	
	public function leave()
	{
		$this->renderer->undent()
			->write('/*]]>*/')
			->write('</style>');
	}
}