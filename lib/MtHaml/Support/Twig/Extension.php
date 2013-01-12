<?php

namespace MtHaml\Support\Twig;

class Extension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'mthaml_attributes' => new \Twig_Function_Function('MtHaml\Runtime::renderAttributes'),
            'mthaml_attribute_interpolation' => new \Twig_Function_Function('MtHaml\Runtime\AttributeInterpolation::create'),
        );
    }

    public function getName()
    {
        return 'mthaml';
    }
}

