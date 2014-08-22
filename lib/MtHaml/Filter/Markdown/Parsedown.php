<?php

namespace MtHaml\Filter\Markdown;

use MtHaml\Filter\Markdown;

class Parsedown extends Markdown
{
    public function __construct(\Parsedown $markdown, $forceOptimization = false)
    {
        parent::__construct($markdown, $forceOptimization);
    }

    public function filter($content, array $context, $options)
    {
        return $this->markdown->text($content);
    }
}
