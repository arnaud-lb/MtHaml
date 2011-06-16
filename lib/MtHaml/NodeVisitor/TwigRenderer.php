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
            if (preg_match('~^(\w+)~', $node->getContent(), $match)) {
                $this->write(sprintf('{%% end%s %%}', $match[1]));
            }
        }
    }

    protected function renderBlockTop(Run $node)
    {
        $this->addDebugInfos($node);
        $this->write(sprintf('{%% %s %%}', $node->getContent()));
    }

    protected function writeDebugInfos($lineno)
    {
        $infos = sprintf('{%% line %d %%}', $lineno);
        $this->raw($infos);
    }
}

