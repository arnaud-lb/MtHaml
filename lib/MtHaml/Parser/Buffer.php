<?php

namespace MtHaml\Parser;

class Buffer
{
    protected $lines;
    protected $line;

    protected $lineno;
    protected $column;

    public function __construct($string, $lineno = 1)
    {
        $this->lines = preg_split('~\r\n|\n|\r~', $string);
        $this->lineno = $lineno - 1;
    }

    public function nextLine()
    {
        $this->line = array_shift($this->lines);
        if (null !== $this->line) {
            ++$this->lineno;
            $this->column = 1;
            return true;
        }
        return false;
    }

    public function peekLine()
    {
        if (isset($this->lines[0])) {
            return $this->lines[0];
        }
    }

    public function replaceLine($string)
    {
        $this->line = $string;
    }

    public function isEol()
    {
        return $this->line === '';
    }

    public function peekChar()
    {
        if (isset($this->line[0])) {
            return $this->line[0];
        }
    }

    public function eatChar()
    {
        if (isset($this->line[0])) {
            $char = $this->line[0];
            $this->line = (string) substr($this->line, 1);
            ++$this->column;
            return $char;
        }
    }

    public function eatChars($n)
    {
        $chars = (string) substr($this->line, 0, $n);
        $this->line = (string) substr($this->line, $n);
        $this->column += strlen($chars);
        return $chars;
    }

    public function match($pattern, &$match = null, $eat = true)
    {
        if ($count = preg_match($pattern, $this->line, $match, PREG_OFFSET_CAPTURE)) {
            $column = $match[0][1];
            $pos = array();

            foreach($match as $key => &$capture) {
                $pos[$key] = array(
                    'lineno' => $this->lineno,
                    'column' => $capture[1],
                );
                $capture = $capture[0];
            }
            unset($capture); // ref

            $match['pos'] = $pos;

            if ($eat) {
                $this->eat($match[0]);
            }
        }
        return $count > 0;
    }

    public function eat($string)
    {
        $this->line = (string) substr($this->line, strlen($string));
        $this->column += strlen($string);
    }

    public function skipWs()
    {
        $this->match('~[ \t]+~A');
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getLineno()
    {
        return $this->lineno;
    }

    public function getPosition()
    {
        return array(
            'lineno' => $this->lineno,
            'column' => $this->column,
        );
    }

    public function getLine()
    {
        return $this->line;
    }
}

