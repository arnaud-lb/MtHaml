<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Insert;
use MtHaml\Node\Run;
use MtHaml\Node\InterpolatedString;
use MtHaml\Node\Tag;

class PhpRenderer extends RendererAbstract
{
    protected function escapeLanguage($string)
    {
        return preg_replace('~(^\?|<\?)~', "<?php echo '\\1'; ?>", $string);
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
            $this->raw(sprintf($fmt, $node->getContent(), $this->charset));
        } else {
            $content = $node->getContent();
            if (!preg_match('~^\$?[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$~', $content)) {
                $this->raw('(' . $node->getContent() . ')');
            } else {
                $this->raw($node->getContent());
            }
        }
    }

    public function enterTopBlock(Run $node)
    {
        $this->addDebugInfos($node);
        if (!$node->hasChilds()) {
            $this->write(sprintf('<?php %s; ?>' , $node->getContent()));
        } else {
            $this->write(sprintf('<?php %s { ?>' , $node->getContent()));
        }
    }

    public function enterMidBlock(Run $node)
    {
        $this->addDebugInfos($node);
        $this->write(sprintf('<?php } %s { ?>' , $node->getContent()));
    }

    public function leaveTopBlock(Run $node)
    {
        if ($node->hasChilds()) {
            $this->write('<?php } ?>');
        }
    }

    protected function writeDebugInfos($lineno)
    {
    }

    protected function renderDynamicAttributes(Tag $tag)
    {
        $list = array();
        $n = 0;

        $this->raw(' <?php echo MtHaml\Runtime::renderAttributes(array(');

        $this->setEchoMode(false);

        foreach ($tag->getAttributes() as $attr) {

            if (0 !== $n) {
                $this->raw(', ');
            }

            $this->raw('array(');
            $attr->getName()->accept($this);
            $this->raw(', ');
            $attr->getValue()->accept($this);
            $this->raw(')');

            ++$n;
        }

        $this->raw(')');

        $this->setEchoMode(true);

        $this->raw(', ');
        $this->raw($this->stringLiteral($this->env->getOption('format')));
        $this->raw(', ');
        $this->raw($this->stringLiteral($this->charset));

        $this->raw('); ?>');
    }
}

