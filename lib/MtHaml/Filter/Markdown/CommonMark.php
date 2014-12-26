<?php

namespace MtHaml\Filter\Markdown;

use League\CommonMark\DocParser;
use League\CommonMark\HtmlRenderer;
use MtHaml\Filter\Markdown;

class CommonMark extends Markdown
{
    private $parser;
    private $renderer;

    public function __construct(DocParser $parser, HtmlRenderer $renderer, $forceOptimization = false)
    {
        parent::__construct($forceOptimization);
        $this->parser = $parser;
        $this->renderer = $renderer;
    }

    public function filter($content, array $context, $options)
    {
        return $this->renderer->renderBlock($this->parser->parse($content));
    }
}
