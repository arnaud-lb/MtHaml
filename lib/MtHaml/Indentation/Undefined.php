<?php

namespace MtHaml\Indentation;

class Undefined implements IndentationInterface
{
    public function newLevel($indent)
    {
        if (0 == strlen($indent)) {
            return $this;
        } else {
            return Indentation::oneLevel($indent);
        }
    }

    public function getChar()
    {
        return null;
    }

    public function getWidth()
    {
        return null;
    }

    public function getLevel()
    {
        return 0;
    }

    public function getString($levelOffset = 0, $fallback = null)
    {
        $char = substr($fallback, 0, 1);
        if (' ' === $char || "\t" === $char) {
            return $char;
        }

        return '';
    }
}

