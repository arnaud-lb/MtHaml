<?php

namespace MtHaml\Filter\Markdown;

use MtHaml\Filter\Markdown;

class MichelfMarkdown extends Markdown
{
    private $markdown;

    public function __construct(\Michelf\Markdown $markdown, $forceOptimization = false)
    {
        parent::__construct($forceOptimization);
        $this->markdown = $markdown;
    }

    public function filter($content, array $context, $options)
    {
        return $this->markdown->transform($content);
    }
}
