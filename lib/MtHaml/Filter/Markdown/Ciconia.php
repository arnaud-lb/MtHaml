<?php

namespace MtHaml\Filter\Markdown;

use MtHaml\Filter\Markdown;

class Ciconia extends Markdown
{
    private $markdown;

    public function __construct(\Ciconia\Ciconia $markdown, $forceOptimization = false)
    {
        parent::__construct($forceOptimization);
        $this->markdown = $markdown;
    }

    public function filter($content, array $context, $options)
    {
        return $this->markdown->render($content);
    }
}
