<?php

namespace MtHaml\Target;

use MtHaml\NodeVisitor\PhpRenderer;
use MtHaml\Environment;

class Php extends TargetAbstract
{
    public function __construct(array $options = array())
    {
        parent::__construct($options + array(
            'midblock_regex' => '~else\b|else\s*if\b~A',
        ));
    }

    public function getDefaultRendererFactory()
    {
        return function(Environment $env, array $options) {
            return new PhpRenderer($env);
        };
    }
}

