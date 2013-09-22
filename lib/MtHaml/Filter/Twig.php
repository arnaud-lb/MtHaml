<?php

namespace MtHaml\Filter;

use MtHaml\Node\Filter;
use MtHaml\NodeVisitor\RendererAbstract as Renderer;
use MtHaml\NodeVisitor\TwigRenderer;

class Twig extends AbstractFilter
{
    private $twig;

    public function __construct(\Twig_Environment $twig = null)
    {
        $this->twig = $twig;
        if (null !== $twig && !function_exists('twig_template_from_string')) {
            $twig->addExtension(new \Twig_Extension_StringLoader());
        }
    }

    public function isOptimizable(Renderer $renderer, Filter $node, $options)
    {
        if (!$renderer instanceof TwigRenderer) {
            return false;
        }

        return parent::isOptimizable($renderer, $node, $options);
    }

    public function optimize(Renderer $renderer, Filter $node, $options)
    {
        foreach ($node->getChilds() as $line) {
            $content = '';
            foreach ($line->getContent()->getChilds() as $child) {
                $content .= $child->getContent();
            }
            $renderer->write($content);
        }
    }

    public function filter($content, array $context, $options)
    {
        $template = twig_template_from_string($this->twig, $content);

        return $template->render($context);
    }
}
