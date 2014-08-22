<?php

namespace MtHaml\Filter;

use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\Node\Filter;

class Less extends AbstractFilter
{
    private $less;

    public function __construct($less)
    {
        if (!is_object($less) || (!is_a($less, 'Less_Parser') && !is_a($less, 'lessc'))) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s::__construct() must be an instance of %s or %s, %s given',
                __CLASS__, 'Less_Parser', 'lessc', is_object($less) ? 'instance of '.get_class($less) : gettype($less)
            ));
        }

        $this->less = $less;
    }

    public function optimize(Renderer $renderer, Filter $node, $options)
    {
        $renderer->write($this->filter($this->getContent($node), array(), $options));
    }

    public function filter($content, array $context, $options)
    {
        if (is_a($this->less, 'Less_Parser')) {
            $this->less->Reset(\Less_Parser::$options);
            $this->less->parse($content);
            $css = $this->less->getCss();
        } else {
            $css = $this->less->compile($content);
        }

        if (isset($options['cdata']) && $options['cdata'] === true) {
            return "<style type=\"text/css\">\n/*<![CDATA[*/\n".$css."\n/*]]>*/\n</style>";
        }

        return "<style type=\"text/css\">\n".$css."\n</style>";
    }
}
