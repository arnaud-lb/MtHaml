<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Insert;
use MtHaml\Node\Run;

class TwigRenderer extends RendererAbstract
{
    protected function escapeLanguage($string)
    {
        return preg_replace('~(^[\{%][\{%]?|\{[\{%])~', "{{ '\\1' }}", $string);
    }

    public function enterInsert(Insert $node)
    {
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
}

