<?php

namespace MtHaml\Filter;

use Gregwar\RST\Parser;

class ReST extends OptimizableFilter
{
    private $parser;

    public function __construct(Parser $parser, $forceOptimization = false)
    {
        parent::__construct($forceOptimization);
        $this->parser = $parser;
    }

    public function filter($content, array $context, $options)
    {
        return $this->parser->parse($content);
    }
}
