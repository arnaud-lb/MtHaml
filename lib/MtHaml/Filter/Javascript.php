<?php

namespace MtHaml\Filter;

class Javascript extends FilterAbstract {
	
	public function enter()
	{
		$this->renderer->write('<script type="text/javascript">')
			->write('//<![CDATA[')
			->indent();
	}
	
	public function leave()
	{
		$this->renderer->undent()
			->write('//]]>')
			->write('</script>');
	}
}