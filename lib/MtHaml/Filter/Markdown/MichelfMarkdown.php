<?php

namespace MtHaml\Filter\Markdown;

use MtHaml\Filter\Markdown;

class MichelfMarkdown extends Markdown
{
    public function __construct(\Michelf\Markdown $markdown, $forceOptimization = false)
    {
        parent::__construct($markdown, $forceOptimization);
    }

    public function filter($content, array $context, $options)
    {
        return $this->markdown->transform($content);
    }
}
