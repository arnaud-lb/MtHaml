<?php

namespace MtHaml\Filter\Markdown;

use cebe\markdown\Parser;
use MtHaml\Filter\Markdown;

class CebeMarkdown extends Markdown
{
    public function __construct(Parser $markdown, $forceOptimization = false)
    {
        parent::__construct($markdown, $forceOptimization);
    }

    public function filter($content, array $context, $options)
    {
        return $this->markdown->parse($content);
    }
}
