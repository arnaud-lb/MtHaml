<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Insert;
use MtHaml\Node\Run;
use MtHaml\Node\InterpolatedString;
use MtHaml\Node\Tag;

class TwigRenderer extends RendererAbstract
{
    protected function escapeLanguage($string)
    {
        return preg_replace('~(^[\{%][\{%]?|\{[\{%])~', "{{ '\\1' }}", $string);
    }

    protected function stringLiteral($string)
    {
        return var_export((string)$string, true);
    }

    public function enterInterpolatedString(InterpolatedString $node)
    {
        if (!$this->isEchoMode() && 1 < count($node->getChilds())) {
            $this->raw('(');
        }
    }

    public function betweenInterpolatedStringChilds(InterpolatedString $node)
    {
        if (!$this->isEchoMode()) {
            $this->raw(' ~ ');
        }
    }

    public function leaveInterpolatedString(InterpolatedString $node)
    {
        if (!$this->isEchoMode() && 1 < count($node->getChilds())) {
            $this->raw(')');
        }
    }

    public function enterInsert(Insert $node)
    {
        if ($this->isEchoMode()) {
            $escaping = $node->getEscaping()->isEnabled();
            if (true === $escaping) {
                $fmt = '{{ (%s)|escape }}';
            } else if (false === $escaping) {
                $fmt = '{{ (%s)|raw }}';
            } else {
                $fmt = '{{ %s }}';
            }
            $this->addDebugInfos($node);
            $this->raw(sprintf($fmt, $node->getContent()));
        } else {
            $content = $node->getContent();
            if (!preg_match('~^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$~', $content)) {
                $this->raw('(' . $node->getContent() . ')');
            } else {
                $this->raw($node->getContent());
            }
        }
    }

    public function enterTopblock(Run $node)
    {
        $this->renderBlockTop($node);
    }

    public function enterMidblock(Run $node)
    {
        $this->renderBlockTop($node);
    }

    public function leaveTopBlock(Run $node)
    {
        if ($node->hasChilds()) {
            if (preg_match('~^(?:-\s*)?(\w+)~', $node->getContent(), $match)) {
                $this->write($this->renderTag('end'.$match[1]));
            }
        }
    }

    protected function renderBlockTop(Run $node)
    {
        $this->addDebugInfos($node);
        $this->write($this->renderTag($node->getContent()));
    }

    protected function renderTag($content)
    {
        $prefix = ' ';
        $suffix = ' ';

        if (preg_match('/^-/', $content)) {
            $prefix = '';
        }
        if (preg_match('/-$/', $content)) {
            $suffix = '';
        }

        return sprintf('{%%%s%s%s%%}', $prefix, $content, $suffix);
    }

    protected function writeDebugInfos($lineno)
    {
        $infos = sprintf('{%% line %d %%}', $lineno);
        $this->raw($infos);
    }

    protected function renderDynamicAttributes(Tag $tag)
    {
        $list = array();

        $this->raw(' ');

        foreach ($tag->getAttributes() as $attr) {
            $this->addDebugInfos($attr);
            break;
        }

        $this->raw('{{ mthaml_attributes([');

        $this->setEchoMode(false);

        foreach (array_values($tag->getAttributes()) as $i => $attr) {

            if (0 !== $i) {
                $this->raw(', ');
            }

            $this->raw('[');
            $attr->getName()->accept($this);
            $this->raw(', ');
            $attr->getValue()->accept($this);
            $this->raw(']');
        }

        $this->raw(']');

        $this->setEchoMode(true);

        $this->raw(', ');
        $this->raw($this->stringLiteral($this->env->getOption('format')));
        $this->raw(', ');
        $this->raw($this->stringLiteral($this->charset));

        $this->raw(')|raw }}');
    }
}

