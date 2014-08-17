<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Filter;
use MtHaml\Node\Insert;
use MtHaml\Node\Run;
use MtHaml\Node\InterpolatedString;
use MtHaml\Node\Tag;
use MtHaml\Node\ObjectRefClass;
use MtHaml\Node\ObjectRefId;
use MtHaml\Node\NodeAbstract;
use MtHaml\Node\TagAttributeInterpolation;
use MtHaml\Node\TagAttributeList;

class TwigRenderer extends RendererAbstract
{
    protected function escapeLanguage($string, $context)
    {
        // If there is a '%' or '{' at the begining of the string, it could
        // become '{%' or '{{' when concatenated with previous output. So we
        // need to escape '{' and '%' when appearing at the begining of the
        // string, unless we know that previous output doesn't end with '{'.
        $re = '~(^[{%][{%]?|\{[{%])~';

        // when context is empty, consider that we don't know what's before
        if (0 < strlen($context)) {
            $len = strlen($context);
            $char = $context[$len-1];
            if ('{' !== $char) {
                $re = '~(\{[{%])~';
            }
        }

        return preg_replace($re, "{{ '\\1' }}", $string);
    }

    protected function stringLiteral($string)
    {
        return var_export((string) $string, true);
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
            } elseif (false === $escaping) {
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
        if ($node->isBlock()) {
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

    public function enterObjectRefClass(ObjectRefClass $node)
    {
        if ($this->isEchoMode()) {
            $this->raw('{{ ');
        }
        $this->raw('mthaml_object_ref_class(');

        $this->pushEchoMode(false);
    }

    public function leaveObjectRefClass(ObjectRefClass $node)
    {
        $this->raw(')');

        $this->popEchoMode(true);
        if ($this->isEchoMode()) {
            $this->raw(' }}');
        }
    }

    public function enterObjectRefId(ObjectRefId $node)
    {
        if ($this->isEchoMode()) {
            $this->raw('{{ ');
        }
        $this->raw('mthaml_object_ref_id(');

        $this->pushEchoMode(false);
    }

    public function leaveObjectRefId(ObjectRefId $node)
    {
        $this->raw(')');

        $this->popEchoMode(true);
        if ($this->isEchoMode()) {
            $this->raw(' }}');
        }
    }

    public function enterObjectRefPrefix(NodeAbstract $node)
    {
        $this->raw(', ');
    }

    public function enterFilter(Filter $node)
    {
        $filter = $this->env->getFilter($node->getFilter());

        if (!$filter->isOptimizable($this, $node, $this->env->getOptions())) {
            $this->write('{% filter mthaml_'.$node->getFilter().' %}', true, false);
            $this->savedIndent[] = $this->indent;
            $this->indent = 0;
        }
    }

    public function leaveFilter(Filter $node)
    {
        $filter = $this->env->getFilter($node->getFilter());

        if (!$filter->isOptimizable($this, $node, $this->env->getOptions())) {
            $this->write('{% endfilter %}');
            $this->indent = $this->popSavedIndent();
        }
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

            if ($attr instanceof TagAttributeInterpolation) {
                $this->raw('mthaml_attribute_interpolation(');
                $attr->getValue()->accept($this);
                $this->raw(')');
            } elseif ($attr instanceof TagAttributeList) {
                $this->raw('mthaml_attribute_list(');
                $attr->getValue()->accept($this);
                $this->raw(')');
            } else {
                $this->raw('[');
                $attr->getName()->accept($this);
                $this->raw(', ');
                if ($attr->getValue()) {
                    $attr->getValue()->accept($this);
                } else {
                    $this->raw('true');
                }
                $this->raw(']');
            }
        }

        $this->raw(']');

        $this->setEchoMode(true);

        $this->raw(', ');
        $this->raw($this->stringLiteral($this->env->getOption('format')));
        $this->raw(', ');
        $this->raw($this->stringLiteral($this->charset));
        $this->raw( ($this->env->getOption('enable_escaper') && $this->env->getOption('escape_attrs'))?
            '' : ', false');

        $this->raw(')|raw }}');
    }
}
