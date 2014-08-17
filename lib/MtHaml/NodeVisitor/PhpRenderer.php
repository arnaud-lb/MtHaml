<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Insert;
use MtHaml\Node\Run;
use MtHaml\Node\InterpolatedString;
use MtHaml\Node\Tag;
use MtHaml\Node\ObjectRefClass;
use MtHaml\Node\NodeAbstract;
use MtHaml\Node\ObjectRefId;
use MtHaml\Node\TagAttributeInterpolation;
use MtHaml\Node\TagAttributeList;
use MtHaml\Node\Filter;

class PhpRenderer extends RendererAbstract
{
    protected function escapeLanguage($string, $context)
    {
        // If there is a '?' at the begining of the string, it could become
        // a '<?' when concatenated with previous output. So we need to escape
        // '?' when appearing at the begining of the string, unless we know
        // that previous output doesn't end with '<'.
        $re = '~(^\?|<\?)~';

        // when context is empty, consider that we don't know what's before
        if (0 < strlen($context)) {
            $len = strlen($context);
            $char = $context[$len-1];
            if ('<' !== $char) {
                $re = '~(<\?)~';
            }
        }

        return preg_replace($re, "<?php echo '\\1'; ?>", $string);
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
            $this->raw(' . ');
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
        $content = $node->getContent();
        $content = $this->trimInlineComments($content);

        if ($this->isEchoMode()) {
            $fmt = '<?php echo %s; ?>';

            if ($node->getEscaping()->isEnabled()) {
                if ($node->getEscaping()->isOnce()) {
                    $fmt = "<?php echo htmlspecialchars(%s,ENT_QUOTES,'%s',false); ?>";
                } else {
                    $fmt = "<?php echo htmlspecialchars(%s,ENT_QUOTES,'%s'); ?>";
                }
            }
            $this->addDebugInfos($node);
            $this->raw(sprintf($fmt, $content, $this->charset));
        } else {
            $content = $node->getContent();
            if (!preg_match('~^\$?[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$~', $content)) {
                $this->raw('(' . $content . ')');
            } else {
                $this->raw($content);
            }
        }
    }

    public function enterTopBlock(Run $node)
    {
        $this->addDebugInfos($node);

        $content = $this->trimInlineComments($node->getContent());

        if (!$node->isBlock()) {
            if (preg_match('~[:;]\s*$~', $content)) {
                $this->write(sprintf('<?php %s ?>' , $content));
            } else {
                $this->write(sprintf('<?php %s; ?>' , $content));
            }
        } else {
            $this->write(sprintf('<?php %s { ?>' , $content));
        }
    }

    public function enterMidBlock(Run $node)
    {
        $this->addDebugInfos($node);

        $content = $this->trimInlineComments($node->getContent());

        $this->write(sprintf('<?php } %s { ?>' , $content));
    }

    public function leaveTopBlock(Run $node)
    {
        if ($node->isBlock()) {
            $this->write('<?php } ?>');
        }
    }

    public function enterObjectRefClass(ObjectRefClass $node)
    {
        if ($this->isEchoMode()) {
            $this->raw('<?php echo ');
        }
        $this->raw('MtHaml\Runtime::renderObjectRefClass(');

        $this->pushEchoMode(false);
    }

    public function leaveObjectRefClass(ObjectRefClass $node)
    {
        $this->raw(')');

        $this->popEchoMode(true);
        if ($this->isEchoMode()) {
            $this->raw('; ?>');
        }
    }

    public function enterObjectRefId(ObjectRefId $node)
    {
        if ($this->isEchoMode()) {
            $this->raw('<?php echo ');
        }
        $this->raw('MtHaml\Runtime::renderObjectRefId(');

        $this->pushEchoMode(false);
    }

    public function leaveObjectRefId(ObjectRefId $node)
    {
        $this->raw(')');

        $this->popEchoMode(true);
        if ($this->isEchoMode()) {
            $this->raw('; ?>');
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
            $this->pushEchoMode(false);
            $this->write('<?php echo MtHaml\Runtime::filter('.$this->env->getOption('mthaml_variable').', '.var_export($node->getFilter(), true).', get_defined_vars(),');
            $this->indent();

            $first = true;
            foreach ($node->getChilds() as $statement) {
                if ($first) {
                    $first = false;
                } else {
                    $this->raw(" .\n");
                }

                $this->writeIndentation();
                $statement->getContent()->accept($this);
                $this->raw('. "\n"');
            }
            $this->raw("\n");

            return false;
        }
    }

    public function leaveFilter(Filter $node)
    {
        $filter = $this->env->getFilter($node->getFilter());

        if (!$filter->isOptimizable($this, $node, $this->env->getOptions())) {
            $this->undent();
            $this->write(') ?>');
            $this->popEchoMode();
        }
    }

    protected function writeDebugInfos($lineno)
    {
    }

    protected function renderDynamicAttributes(Tag $tag)
    {
        $n = 0;

        $this->raw(' <?php echo MtHaml\Runtime::renderAttributes(array(');

        $this->setEchoMode(false);

        foreach ($tag->getAttributes() as $attr) {

            if (0 !== $n) {
                $this->raw(', ');
            }

            if ($attr instanceof TagAttributeInterpolation) {
                $this->raw('MtHaml\Runtime\AttributeInterpolation::create(');
                $attr->getValue()->accept($this);
                $this->raw(')');
            } elseif ($attr instanceof TagAttributeList) {
                $this->raw('MtHaml\Runtime\AttributeList::create(');
                $attr->getValue()->accept($this);
                $this->raw(')');
            } else {
                $this->raw('array(');
                $attr->getName()->accept($this);
                $this->raw(', ');
                if ($attr->getValue()) {
                    $attr->getValue()->accept($this);
                } else {
                    $this->raw('TRUE');
                }
                $this->raw(')');
            }

            ++$n;
        }

        $this->raw(')');

        $this->setEchoMode(true);

        $this->raw(', ');
        $this->raw($this->stringLiteral($this->env->getOption('format')));
        $this->raw(', ');
        $this->raw($this->stringLiteral($this->charset));
        $this->raw( ($this->env->getOption('enable_escaper') && $this->env->getOption('escape_attrs'))?
                    '' : ', false');

        $this->raw('); ?>');
    }

    public function trimInlineComments($code)
    {
        // Removes inlines comments ('//' and '#'), while ignoring '//' and '#'
        // embedded in quoted strings.

        $re = "!
            (?P<code>
                (?P<expr>(?:
                    # anything except \", ', `
                    [^\"'`]

                    # double quoted string
                    | \"(?: [^\"\\\\]+ | \\\\. )*\"

                    # single quoted string
                    | '(?: [^'\\\\]+ | \\\\. )*'

                    # backticks string
                    | `(?: [^`\\\\]+ | \\\\. )*`
                )+?)
            )
            (?P<comment>\s*(?://|\#).*)?
        $!xA";

        return preg_replace($re, '$1', $code);
    }
}
