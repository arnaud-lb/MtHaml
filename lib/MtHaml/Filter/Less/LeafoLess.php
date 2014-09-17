<?php

namespace MtHaml\Filter\Less;

use MtHaml\Filter\Less;

class LeafoLess extends Less
{
    private $less;

    public function __construct(\lessc $less)
    {
        $this->less = $less;
    }

    public function getCss($content, array $context, $options)
    {
        return $this->less->compile($content);
    }
}
