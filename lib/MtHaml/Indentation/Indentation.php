<?php

namespace MtHaml\Indentation;

class Indentation implements IndentationInterface
{
    private $char;
    private $width;
    private $level;

    public static function oneLevel($indent)
    {
        $char = self::getIndentChar($indent);
        $width = strlen($indent);

        return new self($char, $width, $indent);
    }

    public function newLevel($indent)
    {
        if (0 === strlen($indent)) {
            return new self($this->char, $this->width, $indent);
        }

        $char = self::getIndentChar($indent);

        $this->checkSameChar($char);

        $new = new self($this->char, $this->width, $indent);

        if ($new->level > $this->level + 1) {
            throw new IndentationException('The line was indented more than one level deeper than the previous line');
        }

        return $new;
    }

    public function getChar()
    {
        return $this->char;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getString($levelOffset = 0, $fallback = null)
    {
        $length = $this->width * ($this->level + $levelOffset);
        return str_repeat($this->char, $length);
    }

    private static function getIndentChar($indent)
    {
        $char = count_chars($indent, 3 /* 3 = return unique chars */);

        if (1 !== strlen($char)) {
            throw new IndentationException("Indentation can't use both tabs and spaces");
        }

        if (' ' !== $char && "\t" !== $char) {
            throw new IndentationException("Indentation can use only tabs or spaces");
        }

        return $char;
    }

    private function __construct($char, $width, $indent)
    {
        $this->char = $char;
        $this->width = $width;

        if (0 !== (strlen($indent) % $width)) {
            $msg = sprintf('Inconsistent indentation: %d is not a multiple of %d', strlen($indent), $this->width);
            throw new IndentationException($msg);
        }

        $this->level = strlen($indent) / $width;
    }

    private function checkSameChar($char)
    {
        if ($char !== $this->char) {
            $expected = $this->char === ' ' ? 'spaces' : 'tabs';
            $actual = $char === ' ' ? 'spaces' : 'tabs';
            $msg = sprintf('Inconsistent indentation: %s were used for indentation, but the rest of the document was indented using %s', $actual, $expected);
            throw new IndentationException($msg);
        }
    }
}
