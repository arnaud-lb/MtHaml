<?php

namespace MtHaml\Filter\Markdown;

use cebe\markdown\Parser;
use MtHaml\Filter\Markdown;

class CebeMarkdown extends Markdown
{
    private $markdown;

    public function __construct(Parser $markdown, $forceOptimization = false)
    {
        parent::__construct($forceOptimization);
        $this->markdown = $markdown;
    }

    public function filter($content, array $context, $options)
    {
        return $this->markdown->parse($content);
    }
}
