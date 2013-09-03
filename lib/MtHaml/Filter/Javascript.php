<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;

class Javascript extends FilterAbstract {
	
	protected $name = 'javascript';
	
	public function enter(Renderer $renderer, $options)
	{
		$renderer->write('<script type="text/javascript">');
		if ($options['cdata'] === true) {
			$renderer->write('//<![CDATA[');
		}
		$renderer->indent();
	}
		
	public function leave(Renderer $renderer, $options)
	{
		$renderer->undent();
		if ($options['cdata'] === true) {
			$renderer->write('//]]>');
		}
		$renderer->write('</script>');
	}
}