<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Tag;
use MtHaml\Node\TagAttribute;
use MtHaml\Node\Text;
use MtHaml\Node\InterpolatedString;

class MergeAttrs extends NodeVisitorAbstract
{
    protected $attrs;
    protected $tag;

    public function enterTagAttributes(Tag $node)
    {
        $this->attrs = array();
        $this->tag = $node;

        // Do not attempt to merge attributes if any attribute
        // name cannot be guessed at compile time.
        //
        // Else it may change the output (e.g. the unknown attribute
        // name could be 'class')

        foreach ($node->getAttributes() as $attr) {
            if (null === $this->getString($attr->getName())) {
                return false;
            }
        }
    }

    public function enterTagAttribute(TagAttribute $node)
    {
        if (null !== $name = $this->getString($node->getName())) {
            if (isset($this->attrs[$name])) {
                if ('class' === $name) {
                    $orig = $this->attrs[$name]->getValue();
                    $new = $this->mergeClasses($orig, $node->getValue());

                    // Don't merge it if the value isn't const since it could
                    // be an array; which needs special handling at runtime.

                    // Also unset $this->attrs[$name] so that following
                    // class arguments do not get merged into this one.

                    if (!$new->isConst()) {
                        unset($this->attrs[$name]);
                        return;
                    }

                    $this->attrs[$name]->setValue($new);
                    $this->tag->removeAttribute($node);
                } else {
                    $this->tag->removeAttribute($this->attrs[$name]);
                }
            } else {
                $this->attrs[$name] = $node;
            }
        }
    }

    protected function getString($node)
    {
        if ($node instanceof Text) {
            return $node->getContent();
        }
        if ($node instanceof InterpolatedString) {
            $ret = '';
            foreach($node->getChilds() as $child) {
                if (null !== $string = $this->getString($child)) {
                    $ret .= $string;
                } else {
                    return null;
                }
            }
            return $ret;
        }
    }

    protected function mergeClasses($a, $b)
    {
        $new = new InterpolatedString($a->getPosition());
        $this->mergeInto($new, $a);
        $new->addChild(new Text($b->getPosition(), ' '));
        $this->mergeInto($new, $b);
        return $new;
    }

    protected function mergeInto(InterpolatedString $dest, $src)
    {
        if ($src instanceof InterpolatedString) {
            foreach($src->getChilds() as $child) {
                $dest->addChild($child);
            }
        } else {
            $dest->addChild($src);
        }
    }
}

