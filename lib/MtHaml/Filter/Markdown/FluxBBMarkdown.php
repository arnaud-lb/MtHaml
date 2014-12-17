<?php

namespace MtHaml\Filter\Markdown;

use FluxBB\Markdown\Parser;
use MtHaml\Filter\Markdown;

class FluxBBMarkdown extends Markdown
{
    private $markdown;

    public function __construct(Parser $markdown, $forceOptimization = false)
    {
        parent::__construct($forceOptimization);
        $this->markdown = $markdown;
    }

    public function filter($content, array $context, $options)
    {
        return $this->markdown->render($content);
    }
}
