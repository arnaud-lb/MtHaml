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
            new \Twig_SimpleFunction('mthaml_attributes', 'MtHaml\Runtime::renderAttributes'),
            new \Twig_SimpleFunction('mthaml_attribute_interpolation', 'MtHaml\Runtime\AttributeInterpolation::create'),
            new \Twig_SimpleFunction('mthaml_attribute_list', 'MtHaml\Runtime\AttributeList::create'),
            new \Twig_SimpleFunction('mthaml_object_ref_class', 'MtHaml\Runtime::renderObjectRefClass'),
            new \Twig_SimpleFunction('mthaml_object_ref_id', 'MtHaml\Runtime::renderObjectRefId'),
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
