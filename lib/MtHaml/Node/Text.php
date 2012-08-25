<?php

namespace MtHaml\Node;

use MtHaml\Nodevisitor\NodeVisitorInterface;

class Text extends EscapableAbstract
{
    private $content;

    public function __construct(array $position, $content)
    {
        parent::__construct($position);
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getNodeName()
    {
        return 'text';
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        $visitor->enterText($this);
        $visitor->leaveText($this);
    }

    public function isConst()
    {
        return true;
    }
}

