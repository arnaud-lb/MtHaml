<?php

namespace MtHaml;

use MtHaml\Target\Php;
use MtHaml\Target\Twig;
use MtHaml\NodeVisitor\Escaping as EscapingVisitor;
use MtHaml\NodeVisitor\Autoclose;
use MtHaml\NodeVisitor\Midblock;
use MtHaml\NodeVisitor\MergeAttrs;
use MtHaml\Filter\FilterInterface;

class Environment
{
    protected $options = array(
        'format' => 'html5',
        'enable_escaper' => true,
        'escape_html' => true,
        'escape_attrs' => true,
        'cdata' => true,
        'autoclose' => array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'param', 'col', 'base'),
        'charset' => 'UTF-8',
        'enable_dynamic_attrs' => true,
    );

    protected $filters = array(
        'css' => 'MtHaml\\Filter\\Css',
        'cdata' => 'MtHaml\\Filter\\Cdata',
        'escaped' => 'MtHaml\\Filter\\Escaped',
        'javascript' => 'MtHaml\\Filter\\Javascript',
        'php' => 'MtHaml\\Filter\\Php',
        'plain' => 'MtHaml\\Filter\\Plain',
        'preserve' => 'MtHaml\\Filter\\Preserve',
        'twig' => 'MtHaml\\Filter\\Twig',
    );

    protected $target;

    public function __construct($target, array $options = array(), $filters = array())
    {
        $this->target = $target;
        $this->options = $options + $this->options;
        $this->filters = $filters + $this->filters;
    }

    public function compileString($string, $filename)
    {
        $target = $this->getTarget();

        $node = $target->parse($this, $string, $filename);

        foreach ($this->getVisitors() as $visitor) {
            $node->accept($visitor);
        }

        $code = $target->compile($this, $node, $filename);

        return $code;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name)
    {
        return $this->options[$name];
    }

    /**
     * Returns a filter
     *
     * @param $name A name of filter
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return FilterInterface
     */
    public function getFilter($name)
    {
        if (!isset($this->filters[$name])) {
            throw new \InvalidArgumentException(sprintf('Unknown filter name "%s"', $name));
        }

        $filter = $this->filters[$name];

        if (is_string($filter)) {
            if (!class_exists($filter)) {
                throw new \RuntimeException(sprintf('Class "%s" for filter "%s" does not exists', $filter, $name));
            }

            $filter = new $filter;
            $this->addFilter($name, $filter);
        }

        return $filter;
    }

    public function addFilter($name, $filter)
    {
        if (!is_string($filter) && !(is_object($filter) && $filter instanceof FilterInterface)) {
            throw new \InvalidArgumentException('Filter should be a class name or an instance of FilterInterface');
        }

        $this->filters[$name] = $filter;

        return $this;
    }

    public function getTarget()
    {
        $target = $this->target;
        if (is_string($target)) {
            switch ($target) {
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
        } elseif (!$this->getOption('escape_attrs')) {
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
