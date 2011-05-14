<?php

namespace MtHaml;

use MtHaml\Target\Php;
use MtHaml\Target\Twig;
use MtHaml\NodeVisitor\Escaping as EscapingVisitor;
use MtHaml\NodeVisitor\Autoclose;
use MtHaml\NodeVisitor\Midblock;
use MtHaml\NodeVisitor\MergeAttrs;

class Environment
{
    protected $options = array(
        'format' => 'html5',
        'enable_escaper' => true,
        'escape_html' => true,
        'escape_attrs' => true,
        'autoclose' => array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'param', 'col', 'base'),
        'charset' => 'UTF-8',
    );

    protected $target;

    public function __construct($target, array $options = array())
    {
        $this->target = $target;
        $this->options = $options + $this->options;
    }

    public function compileString($string, $filename)
    {
        $target = $this->getTarget();

        $node = $target->parse($this, $string, $filename);

        foreach($this->getVisitors() as $visitor) {
            $node->accept($visitor);
        }

        $code = $target->compile($this, $node, $filename);

        return $code;
    }

    public function getOption($name)
    {
        return $this->options[$name];
    }

    public function getTarget()
    {
        $target = $this->target;
        if (is_string($target)) {
            switch($target) {
            case 'php':
                $target = new Php;
                break;
            case 'twig':
                $target = new Twig;
                break;
            default:
                throw new Exception(sprintf('Unknown target language: %s', $target));
            }
            $this->target = $target;
        }
        return $target;
    }

    public function getVisitors()
    {
        $visitors = array();

        $visitors[] = $this->getAutoclosevisitor();
        $visitors[] = $this->getMidblockVisitor();
        $visitors[] = $this->getMergeAttrsVisitor();

        if ($this->getOption('enable_escaper')) {
            $visitors[] = $this->getEscapingVisitor();
        }

        return $visitors;
    }

    public function getEscapingVisitor()
    {
        $html = EscapingVisitor::ESCAPE_TRUE;
        if (!$this->getOption('escape_html')) {
            $html = EscapingVisitor::ESCAPE_FALSE;
        }

        $attrs = EscapingVisitor::ESCAPE_TRUE;
        if ('once' === $this->getOption('escape_attrs')) {
            $attrs = EscapingVisitor::ESCAPE_ONCE;
        } else if (!$this->getOption('escape_attrs')) {
            $attrs = EscapingVisitor::ESCAPE_FALSE;
        }

        return new EscapingVisitor($html, $attrs);
    }

    public function getAutocloseVisitor()
    {
        return new Autoclose($this->getOption('autoclose'));
    }

    public function getMidblockVisitor()
    {
        return new Midblock($this->getTarget()->getOption('midblock_regex'));
    }

    public function getMergeAttrsVisitor()
    {
        return new MergeAttrs;
    }
}

