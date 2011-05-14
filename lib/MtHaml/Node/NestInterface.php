<?php

namespace MtHaml\Node;

interface NestInterface
{
    public function addChild(NodeAbstract $child);
    public function hasContent();
    public function allowsNestingAndContent();
}

