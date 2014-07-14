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
     * @param array  $list    A list of attributes (items are array($name, $value))
     * @param string $format  Output format (e.g. html5)
     * @param string $charset Output charset
     */
    public static function renderAttributes($list, $format, $charset, $escape = true)
    {
        $attributes = array();

        self::mergeAttributes($attributes, $list, $format);

        $result = null;

        foreach ($attributes as $name => $value) {
            if (null !== $result) {
                $result .= ' ';
            }
            if ($value instanceof AttributeInterpolation) {
                $result .= $value->value;
            } elseif (true === $value) {
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

    private static function mergeAttributes(&$dest, $list, $format)
    {
        foreach ($list as $item) {

            if ($item instanceof AttributeInterpolation) {
                $dest[] = $item;
                continue;
            } elseif ($item instanceof AttributeList) {
                $pairs = array();
                foreach ($item->attributes as $name => $value) {
                    $pairs[] = array($name, $value);
                }
                self::mergeAttributes($dest, $pairs, $format);
                continue;
            }

            list ($name, $value) = $item;

            if ('data' === $name) {
                self::renderDataAttributes($dest, $value);
            } elseif ('id' === $name) {
                $value = self::renderJoinedValue($value, '_');
                if (null !== $value) {
                    if (isset($dest['id'])) {
                        $dest['id'] .= '_' . $value;
                    } else {
                        $dest['id'] = $value;
                    }
                }
            } elseif ('class' === $name) {
                $value = self::renderJoinedValue($value, ' ');
                if (null !== $value) {
                    if (isset($dest['class'])) {
                        $dest['class'] .= ' ' . $value;
                    } else {
                        $dest['class'] = $value;
                    }
                }
            } elseif (true === $value) {
                if ('html5' === $format) {
                    $dest[$name] = true;
                } else {
                    $dest[$name] = $name;
                }
            } elseif (false === $value || null === $value) {
                // do not output
            } else {
                if (isset($dest[$name])) {
                    // so that next assignment puts the attribute
                    // at the end for the array
                    unset($dest[$name]);
                }
                $dest[$name] = $value;
            }
        }
    }

    private static function renderDataAttributes(&$dest, $value, $prefix = 'data')
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

    private static function renderJoinedValue($values, $separator)
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

    public static function renderObjectRefClass($object, $prefix = null)
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

    public static function renderObjectRefId($object, $prefix = null)
    {
        if (!$object) {
            return;
        }

        $id = null;

        if (\is_callable(array($object, 'getId'))) {
            $id = $object->getId();
        } elseif (\is_callable(array($object, 'id'))) {
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

    public static function getObjectRefClassString($object)
    {
        $class = self::getObjectRefName($object);
        if (false !== $pos = \strrpos($class, '\\')) {
            $class = \substr($class, $pos+1);
        }

        return \strtolower(\preg_replace('#(?<=[a-z])[A-Z]+#', '_$0', $class));
    }

    public static function getObjectRefName($object)
    {
        return \is_callable(array($object, 'hamlObjectRef'))
            ? $object->hamlObjectRef()
            : \get_class($object);
    }

    public static function filter(Environment $mthaml, $filter, array $context, $content)
    {
        return $mthaml->getFilter($filter)->filter($content, $context, $mthaml->getOptions());
    }
}
