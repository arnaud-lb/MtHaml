<?php

namespace MtHaml\Runtime;

class AttributeInterpolation
{
    public $value;

    public static function create($value)
    {
        $instance = new AttributeInterpolation;
        $instance->value = $value;

        return $instance;
    }
}
