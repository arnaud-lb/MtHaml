<?php

namespace MtHaml\Target;

use MtHaml\NodeVisitor\TwigRenderer;
use MtHaml\Environment;

class Twig extends TargetAbstract
{
    public function __construct(array $options = array())
    {
        parent::__construct($options + array(
            'midblock_regex' => '/(?:-\s*)?(?:else\b|elseif\b)/A',
        ));
    }

    public function getDefaultRendererFactory()
    {
        return function (Environment $env, array $options) {
            return new TwigRenderer($env);
        };
    }
}
