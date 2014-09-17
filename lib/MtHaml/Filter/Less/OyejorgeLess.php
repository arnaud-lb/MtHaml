<?php

namespace MtHaml\Filter\Less;

use MtHaml\Filter\Less;

class OyejorgeLess extends Less
{
    private $less;

    public function __construct(\Less_Parser $less)
    {
        $this->less = $less;
    }

    protected function getCss($content, array $context, $options)
    {
        $this->less->Reset(\Less_Parser::$options);
        $this->less->parse($content);

        return $this->less->getCss();
    }
}
