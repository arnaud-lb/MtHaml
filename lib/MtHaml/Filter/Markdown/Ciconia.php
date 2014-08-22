<?php

namespace MtHaml\Filter\Markdown;

use MtHaml\Filter\Markdown;

class Ciconia extends Markdown
{
    public function __construct(\Ciconia\Ciconia $markdown, $forceOptimization = false)
    {
        parent::__construct($markdown, $forceOptimization);
    }

    public function filter($content, array $context, $options)
    {
        return $this->markdown->render($content);
    }
}
