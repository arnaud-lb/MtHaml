<?php

namespace MtHaml\Filter\Markdown;

use FluxBB\CommonMark\DocumentParser;
use FluxBB\CommonMark\Renderer;
use MtHaml\Filter\Markdown;

class FluxBBMarkdown extends Markdown
{
    private $parser;
    private $renderer;

    public function __construct(DocumentParser $parser, Renderer $renderer, $forceOptimization = false)
    {
        parent::__construct($forceOptimization);
        $this->parser = $parser;
        $this->renderer = $renderer;
    }

    public function filter($content, array $context, $options)
    {
        return $this->renderer->render($this->parser->convert($content));
    }
}
