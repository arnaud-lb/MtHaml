<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Tag;

class Autoclose extends NodeVisitorAbstract
{
    protected $autocloseTags;

    public function __construct(array $autocloseTags)
    {
        $this->autocloseTags = $autocloseTags;
    }

    public function enterTag(Tag $tag)
    {
        if ($tag->hasChilds() || $tag->hasContent()) {
            return;
        }
        if (in_array($tag->getTagName(), $this->autocloseTags)) {
            $tag->setFlag(Tag::FLAG_SELF_CLOSE);
        }
    }
}

