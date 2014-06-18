<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

class Tag extends NestAbstract
{
    const FLAG_REMOVE_INNER_WHITESPACES = 1;
    const FLAG_REMOVE_OUTER_WHITESPACES = 2;
    const FLAG_SELF_CLOSE = 4;

    protected $tagName;
    protected $attributes;
    protected $flags;

    public function __construct(array $position, $tagName, array $attributes, $flags = 0)
    {
        parent::__construct($position);
        $this->tagName = $tagName;
        $this->attributes = $attributes;
        $this->flags = $flags;
    }

    public function getTagName()
    {
        return $this->tagName;
    }

    public function addAttribute(TagAttribute $attribute)
    {
        $this->attributes[] = $attribute;
    }

    public function hasAttributes()
    {
        return 0 < count($this->attributes);
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function removeAttribute(TagAttribute $attribute)
    {
        if (null !== $key = array_search($attribute, $this->attributes, true)) {
            unset($this->attributes[$key]);
        }
    }

    public function setFlag($flag)
    {
        $this->flags |= $flag;
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function getNodeName()
    {
        return 'tag';
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        if (false !== $visitor->enterTag($this)) {

            if (false !== $visitor->enterTagAttributes($this)) {
                foreach ($this->getAttributes() as $attribute) {
                    $attribute->accept($visitor);
                }
            }
            $visitor->leaveTagAttributes($this);

            if (false !== $visitor->enterTagContent($this)) {
                $this->visitContent($visitor);
            }
            $visitor->leaveTagContent($this);

            if (false !== $visitor->enterTagChilds($this)) {
                $this->visitChilds($visitor);
            }
            $visitor->leaveTagChilds($this);
        }
        $visitor->leaveTag($this);
    }
}
