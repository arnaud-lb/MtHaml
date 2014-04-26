<?php

namespace MtHaml;

use MtHaml\Runtime\AttributeInterpolation;
use MtHaml\Runtime\AttributeList;

class Runtime
{
    /**
     * Renders a list of attributes
     *
     * Handles the following special cases:
     * - attribute named 'data' with iterable value it rendered as multiple
     *   html5 data- attributes
     * - attribute with boolean value is rendered as boolean attribute. In html5
     *   format, only the attribute name is rendered, like 'checked'. In
     *   other formats, it is rendered with the attribute name as value, like
     *   'checked="checked"'.
     * - attribute with null or false value isn't rendered
     * - multiple id attributes and id attributes with iterable values are
     *   rendered concatenated with underscores
     * - multiple class attributes and class attributes with iterable values are
     *   rendered concatenated with spaces
     *
     * @param array $list A list of attributes (items are array($name, $value))
     * @param string $format Output format (e.g. html5)
     * @param string $charset Output charset
     */
    static public function renderAttributes($list, $format, $charset, $escape = true)
    {
        $attributes = array();

        foreach ($list as $item) {

            if ($item instanceof AttributeInterpolation) {
                $attributes[] = $item;
                continue;
            } else if ($item instanceof AttributeList) {
                $attributes = array_merge($attributes, $item->attributes);
                continue;
            }

            list ($name, $value) = $item;

            if ('data' === $name) {
                self::renderDataAttributes($attributes, $value);
            } else if ('id' === $name) {
                $value = self::renderJoinedValue($value, '_');
                if (null !== $value) {
                    if (isset($attributes['id'])) {
                        $attributes['id'] .= '_' . $value;
                    } else {
                        $attributes['id'] = $value;
                    }
                }
            } else if ('class' === $name) {
                $value = self::renderJoinedValue($value, ' ');
                if (null !== $value) {
                    if (isset($attributes['class'])) {
                        $attributes['class'] .= ' ' . $value;
                    } else {
                        $attributes['class'] = $value;
                    }
                }
            } else if (true === $value) {
                if ('html5' === $format) {
                    $attributes[$name] = true;
                } else {
                    $attributes[$name] = $name;
                }
            } else if (false === $value || null === $value) {
                // do not output
            } else {
                if (isset($attributes[$name])) {
                    // so that next assignment puts the attribute
                    // at the end for the array
                    unset($attributes[$name]);
                }
                $attributes[$name] = $value;
            }
        }

        $result = null;

        foreach ($attributes as $name => $value) {
            if (null !== $result) {
                $result .= ' ';
            }
            if ($value instanceof AttributeInterpolation) {
                $result .= $value->value;
            } else if (true === $value) {
                $result .= $escape ?
                    htmlspecialchars($name, ENT_QUOTES, $charset) : $name;
            } else {
                $result .= ($escape ?
                        htmlspecialchars($name, ENT_QUOTES, $charset) : $name)
                    .'="'
                    . ($escape ?
                        htmlspecialchars($value, ENT_QUOTES, $charset) : $value)
                    .'"';
            }
        }

        return $result;
    }

    static private function renderDataAttributes(&$dest, $value, $prefix = 'data')
    {
        if (\is_array($value) || $value instanceof \Traversable) {
            foreach ($value as $subname => $subvalue) {
                self::renderDataAttributes($dest, $subvalue, $prefix.'-'.$subname);
            }
        } else {
            if (!isset($dest[$prefix])) {
                $dest[$prefix] = $value;
            }
        }
    }

    static private function renderJoinedValue($values, $separator)
    {
        $result = null;

        if (\is_array($values) || $values instanceof \Traversable) {
            foreach ($values as $value) {
                if (\is_array($value) || $value instanceof \Traversable) {
                    $value = self::renderJoinedValue($value, $separator);
                }
                if (null !== $value && false !== $value) {
                    if (null !== $result) {
                        $result .= $separator;
                    }
                    $result .= $value;
                }
            }
        } else {
            if (null !== $values && false !== $values) {
                $result = $values;
            }
        }

        return $result;
    }

    static public function renderObjectRefClass($object, $prefix = null)
    {
        if (!$object) {
            return;
        }

        $class = self::getObjectRefClassString($object);

        if (false !== $prefix && null !== $prefix) {
            $class = $prefix . '_' . $class;
        }

        return $class;
    }

    static public function renderObjectRefId($object, $prefix = null)
    {
        if (!$object) {
            return;
        }

        $id = null;

        if (\is_callable(array($object, 'getId'))) {
            $id = $object->getId();
        } else if (\is_callable(array($object, 'id'))) {
            $id = $object->id();
        }

        if (false === $id || null === $id) {
            $id = 'new';
        }

        $id = self::getObjectRefClassString($object) . '_' . $id;

        if (false !== $prefix && null !== $prefix) {
            $id = $prefix . '_' . $id;
        }

        return $id;
    }

    static public function getObjectRefClassString($object)
    {
        $class = self::getObjectRefName($object);
        if (false !== $pos = \strrpos($class, '\\')) {
            $class = \substr($class, $pos+1);
        }
        return \strtolower(\preg_replace('#(?<=[a-z])[A-Z]+#', '_$0', $class));
    }

    static public function getObjectRefName($object)
    {
        return \is_callable(array($object, 'hamlObjectRef'))
            ? $object->hamlObjectRef()
            : \get_class($object);
    }

    static public function filter(Environment $mthaml, $filter, array $context, $content)
    {
        return $mthaml->getFilter($filter)->filter($content, $context, $mthaml->getOptions());
    }
}
