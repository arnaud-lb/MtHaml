<?php

namespace MtHaml\Filter\Markdown;

use MtHaml\Filter\Markdown;

class Parsedown extends Markdown
{
    private $markdown;

    public function __construct(\Parsedown $markdown, $forceOptimization = false)
    {
        parent::__construct($forceOptimization);
        $this->markdown = $markdown;
    }

    public function filter($content, array $context, $options)
    {
        return $this->markdown->text($content);
    }
}
