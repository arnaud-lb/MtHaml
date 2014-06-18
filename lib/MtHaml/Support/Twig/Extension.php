<?php

namespace MtHaml\Support\Twig;

use MtHaml\Environment;

class Extension extends \Twig_Extension
{
    private $mthaml;

    public function __construct(Environment $mthaml = null)
    {
        $this->mthaml = $mthaml;
    }

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

    public function getFilters()
    {
        if (null === $this->mthaml) {
            return array();
        }

        return array(
            new \Twig_SimpleFilter('mthaml_*', array($this, 'filter'), array('needs_context' => true, 'is_safe' => array('html'))),
        );
    }

    public function filter(array $context, $name, $content)
    {
        return $this->mthaml->getFilter($name)->filter($content, $context, $this->mthaml->getOptions());
    }

    public function getName()
    {
        return 'mthaml';
    }
}
