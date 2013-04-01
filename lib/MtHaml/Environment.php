<?php

namespace MtHaml;

use MtHaml\Target\Php;
use MtHaml\Target\Twig;
use MtHaml\NodeVisitor\Escaping as EscapingVisitor;
use MtHaml\NodeVisitor\Autoclose;
use MtHaml\NodeVisitor\Midblock;
use MtHaml\NodeVisitor\MergeAttrs;
use MtHaml\Filter\FilterProvider;
use ArrayObject;

class Environment
{
    protected $options = array(
        'format' => 'html5',
        'enable_escaper' => true,
        'escape_html' => true,
        'escape_attrs' => true,
        'cdata'	=> true,
        'autoclose' => array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'param', 'col', 'base'),
        'charset' => 'UTF-8'
    );

    protected $target;
    
    protected $filter;

    public function __construct($target, array $options = array(), array $filters = array())
    {
        $this->target	= $target;
        $this->options 	= new ArrayObject($options + $this->options);
        $this->filter 	= new FilterProvider($filters);
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
    
    public function addFilter($name, $nameOrClass = null)
    {
    	return $this->filter->set($name, $nameOrClass);
    }
    
    public function getFilter($name = null)
    {
	    return $this->filter->get($name);
    }

    public function getOption($name = null)
    {
    	if (null !== $name) {
	    	return $this->options[$name];
    	}
        return $this->options;
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

