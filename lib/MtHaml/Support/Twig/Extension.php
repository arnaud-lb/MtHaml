<?php

namespace MtHaml\Support\Twig;

class Extension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'mthaml_attributes' => new \Twig_Function_Function('MtHaml\Runtime::renderAttributes'),
        );
    }

    public function getName()
    {
        return 'mthaml';
    }
}

