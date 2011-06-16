<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Insert;
use MtHaml\Node\Run;

class PhpRenderer extends RendererAbstract
{
    protected function escapeLanguage($string)
    {
        return preg_replace('~(^\?|<\?)~', "<?php echo '\\1'; ?>", $string);
    }

    public function enterInsert(Insert $node)
    {
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
}

