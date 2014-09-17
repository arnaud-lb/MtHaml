<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;

class Scss extends AbstractFilter
{
    private $scss;

    public function __construct($scss)
    {
        if (!is_object($scss) || (!is_a($scss, 'Leafo\ScssPhp\Compiler') && !is_a($scss, 'scssc'))) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s::__construct() must be an instance of %s or %s, %s given',
                __CLASS__, 'Leafo\ScssPhp\Compiler', 'scssc', is_object($scss) ? 'instance of '.get_class($scss) : gettype($scss)
            ));
        }

        $this->scss = $scss;
    }

    public function optimize(Renderer $renderer, Filter $node, $options)
    {
        $renderer->write($this->filter($this->getContent($node), array(), $options));
    }

    public function filter($content, array $context, $options)
    {
        if (isset($options['cdata']) && $options['cdata'] === true) {
            return "<style type=\"text/css\">\n/*<![CDATA[*/\n".$this->scss->compile($content)."\n/*]]>*/\n</style>";
        }

        return "<style type=\"text/css\">\n".$this->scss->compile($content)."\n</style>";
    }
}
