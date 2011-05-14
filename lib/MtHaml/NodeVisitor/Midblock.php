<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Run;

class Midblock extends NodeVisitorAbstract
{
    protected $midblockRegex;

    public function __construct($midblockRegex)
    {
        $this->midblockRegex = $midblockRegex;
        $this->skip = new \SplObjectStorage;
    }

    public function enterRun(Run $node)
    {
        do {
            if (null === $prev = $node->getPreviousSibling()) {
                break;
            }
            if (!$prev instanceof Run) {
                break;
            }
            if (!preg_match($this->midblockRegex, $node->getContent())) {
                break;
            }

            $node->getParent()->removeChild($node);
            while (null !== $prev->getMidblock()) {
                $prev = $prev->getMidblock();
            }
            $prev->setMidblock($node);
        } while (false);
    }
}

