<?php

namespace MtHaml\Support\Twig;

class Extension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'mthaml_attributes' => new \Twig_Function_Function('MtHaml\Runtime::renderAttributes'),
            'mthaml_attribute_interpolation' => new \Twig_Function_Function('MtHaml\Runtime\AttributeInterpolation::create'),
            'mthaml_attribute_list' => new \Twig_Function_Function('MtHaml\Runtime\AttributeList::create'),
            'mthaml_object_ref_class' => new \Twig_Function_Function('MtHaml\Runtime::renderObjectRefClass'),
            'mthaml_object_ref_id' => new \Twig_Function_Function('MtHaml\Runtime::renderObjectRefId'),
        );
    }

    public function getName()
    {
        return 'mthaml';
    }
}

