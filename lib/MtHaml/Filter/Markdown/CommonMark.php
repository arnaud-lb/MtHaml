<?php

namespace MtHaml\Filter\Markdown;

use League\CommonMark\Converter;
use MtHaml\Filter\Markdown;

class CommonMark extends Markdown
{
    private $converter;

    public function __construct(Converter $converter, $forceOptimization = false)
    {
        parent::__construct($forceOptimization);
        $this->converter = $converter;
    }

    public function filter($content, array $context, $options)
    {
        return $this->converter->convertToHtml($content);
    }
}
