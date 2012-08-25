<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Tag;
use MtHaml\Node\TagAttribute;
use MtHaml\Node\Statement;
use MtHaml\Node\Text;
use MtHaml\Node\InterpolatedString;
use MtHaml\Node\Doctype;
use MtHaml\Node\Comment;
use MtHaml\Environment;
use MtHaml\Node\Filter;
use MtHaml\Exception;
use MtHaml\Node\NodeAbstract;
use MtHaml\Node\NestInterface;
use MtHaml\Node\Run;

abstract class RendererAbstract extends NodeVisitorAbstract
{
    protected $indent;
    protected $savedIndent = array();
    protected $output = '';
    protected $lineno = 1;
    protected $lineOffset = 0;

    protected $prevFlags = 0;

    protected $env;
    protected $charset = 'UTF-8';

    protected $midblock = array(false);

    /**
     * Whether echo mode is enabled
     *
     * In echo mode, nodes such as Text or Insert are directly printed with
     * e.g. <?php echo $var; ?> in the PHP renderer.
     *
     * In non echo mode, nodes are rendered as rvalue (e.g. for assignment or
     * argument passing).
     */
    protected $echoMode = true;

    public function __construct(Environment $env)
    {
        $this->env = $env;
        $this->charset = $env->getOption('charset');
    }

    public function getOutput()
    {
        return $this->output;
    }

    protected function indent()
    {
        $this->indent += 1;
        return $this;
    }

    protected function undent()
    {
        $this->indent -= 1;
        return $this;
    }

    protected function write($string, $indent = true, $break = true)
    {
        if ($indent) {
            $this->writeIndentation();
        }
        $this->raw($string);
        $this->lineno += substr_count($string, "\n");
        if ($break) {
            $this->output .= "\n";
            $this->lineno++;
        }
        return $this;
    }

    protected function raw($string)
    {
        $this->output .= $string;
        $this->lineno += substr_count($string, "\n");
        return $this;
    }

    protected function writeIndentation()
    {
        $this->output .= str_repeat(' ', $this->indent * 2);
        return $this;
    }

    protected function addDebugInfos(NodeAbstract $node)
    {
        if ($this->lineno != $node->getLineno() + $this->lineOffset) {
            $this->writeDebugInfos($node->getLineno());
            $this->lineOffset = $this->lineno - $node->getLineno();
        }
    }

    abstract protected function writeDebugInfos($lineno);

    abstract protected function escapeLanguage($string);

    abstract protected function stringLiteral($string);

    abstract protected function betweenInterpolatedStringChilds(InterpolatedString $node);

    protected function escapeHtml($string, $double = true)
    {
        return htmlspecialchars($string, ENT_QUOTES, $this->charset, $double);
    }

    public function enterTag(Tag $node)
    {
        $indent = $this->shouldIndentBeforeOpen($node);

        $this->write(sprintf('<%s', $node->getTagName()), $indent, false);
    }

    public function enterTagAttributes(Tag $node)
    {
        $hasDynAttr = false;

        foreach ($node->getAttributes() as $attr) {
            $nameNode = $attr->getName();
            $valueNode = $attr->getValue();

            if (!$nameNode->isConst() || !$valueNode->isConst()) {
                $hasDynAttr = true;
                break;
            }
        }

        if (!$hasDynAttr) {
            return;
        }

        $this->renderDynamicAttributes($node);

        return false;
    }

    public function leaveTagAttributes(Tag $node)
    {
        $close = $node->getFlags() & Tag::FLAG_SELF_CLOSE;

        if ($close) {
            $break = $this->shouldBreakAfterClose($node);
        } else {
            $break = $this->shouldBreakAfterOpen($node);
        }

        $this->write(($close ? ' /' : '') . '>', false, $break);

        if (!$close && $break) {
            $this->indent();
        }
    }

    public function enterTagAttributeName(TagAttribute $node)
    {
        $this->raw(' ');
    }

    public function enterTagAttributeValue(TagAttribute $node)
    {
        $this->raw('="');
    }

    public function leaveTagAttributeValue(TagAttribute $node)
    {
        $this->raw('"');
    }

    public function leaveTag(Tag $node)
    {
        if ($node->getFlags() & Tag::FLAG_SELF_CLOSE) {
            return;
        }

        $indent = $this->shouldIndentBeforeClose($node);
        $break = $this->shouldBreakAfterClose($node);

        if ($this->shouldBreakAfterOpen($node)) {
            $this->undent();
        }

        $this->write(sprintf('</%s>', $node->getTagName()), $indent, $break);
    }

    public function enterStatement(Statement $node)
    {
        if ($this->shouldIndentBeforeOpen($node)) {
            $this->writeIndentation();
        }
    }

    public function leaveStatement(Statement $node)
    {
        if ($this->shouldBreakAfterClose($node)) {
            $this->raw("\n");
        }
    }

    public function enterText(Text $node)
    {
        $string = $node->getContent();

        if ($this->isEchoMode()) {
            if ($node->getEscaping()->isEnabled()) {
                $once = $node->getEscaping()->isOnce();
                $string = $this->escapeHtml($string, !$once);
            }

            $string = $this->escapeLanguage($string);
            $this->raw($string);
        } else {
            $string = $this->stringLiteral($string);
            $this->raw($string);
        }
    }

    public function enterInterpolatedStringChilds(InterpolatedString $node)
    {
        $n = 0;

        foreach ($node->getChilds() as $child) {
            if (0 !== $n) {
                $this->betweenInterpolatedStringChilds($node);
            }
            $child->accept($this);
            ++$n;
        }

        return false;
    }

    public function enterDoctype(Doctype $node)
    {
        $this->write($node->getDoctype($this->env->getOption('format')));
    }

    public function enterComment(Comment $comment)
    {
        if (!$comment->isRendered()) {
            return false;
        }

        if ($comment->hasCondition()) {
            $open = '<!--' . $comment->getCondition() . '>';
        } else {
            $open = '<!--';
        }

        if ($comment->hasContent()) {
            $this->write($open . ' ', $comment->hasParent(), false);
        } else if ($comment->hasChilds()) {
            $this->write($open, true, true)->indent();
        }
    }

    public function leaveComment(Comment $comment)
    {
        if (!$comment->isRendered()) {
            return false;
        }

        if ($comment->hasCondition()) {
            $close = '<![endif]-->';
        } else {
            $close = '-->';
        }

        if ($comment->hasContent()) {
            $this->write(' ' . $close, false, $comment->hasParent());
        } else if ($comment->hasChilds()) {
            $this->undent()->write($close, true, true);
        }
    }

    public function enterFilter(Filter $node)
    {
        // TODO: make filters modular

        switch($node->getFilter()) {
        case 'javascript':
            $this->write('<script type="text/javascript">')
                ->write('//<![CDATA[')
                ->indent();
            break;
        case 'css':
            $this->write('<style type="text/css">')
                ->write('/*<![CDATA[*/')
                ->indent();
            break;
        case 'plain':
            break;
        case 'preserve':
            $this->savedIndent[] = $this->indent;
            $this->indent = 0;
            break;
        default:
            throw new Exception("unknown filter " . $node->getFilter());
        }
    }

    public function leaveFilter(Filter $node)
    {
        switch($node->getFilter()) {
        case 'javascript':
            $this->undent()
                ->write('//]]>')
                ->write('</script>');
            break;
        case 'css':
            $this->undent()
                ->write('/*]]>*/')
                ->write('</style>');
            break;
        case 'plain':
            break;
        case 'preserve':
            $this->indent = array_pop($this->savedIndent);
            break;
        }
    }

    public function enterRun(Run $node)
    {
        $isMidBlock = $this->midblock[0];
        array_unshift($this->midblock, false);

        if (!$isMidBlock) {
            $this->enterTopblock($node);
        } else {
            $this->enterMidblock($node);
        }
    }

    public function enterRunChilds(Run $node)
    {
        $this->indent();
    }

    public function leaveRunChilds(Run $node)
    {
        $this->undent();
    }

    public function enterRunMidblock(Run $node)
    {
        array_unshift($this->midblock, true);
    }

    public function leaveRunMidblock(Run $node)
    {
        array_shift($this->midblock);
    }

    public function leaveRun(Run $node)
    {
        array_shift($this->midblock);

        if (!$this->midblock[0]) {
            $this->leaveTopblock($node);
        } else {
            $this->leaveMidblock($node);
        }
    }

    public function enterTopblock(Run $node)
    {
    }
    public function leaveTopblock(Run $node)
    {
    }
    public function enterMidblock(Run $node)
    {
    }
    public function leaveMidblock(Run $node)
    {
    }

    protected function getParentIfFirstChild(NodeAbstract $node)
    {
        if (null !== $node->getPreviousSibling()) {
            return;
        }
        return $this->getParentTag($node);
    }

    protected function getParentIfLastChild(NodeAbstract $node)
    {
        if (null !== $node->getNextSibling()) {
            return;
        }
        return $this->getParentTag($node);
    }

    protected function getParentTag(NodeAbstract $node)
    {
        if (null !== $parent = $node->getParent()) {
            if ($parent instanceof Tag) {
                return $parent;
            }
        }
    }

    protected function getFirstChildIfTag(NodeAbstract $node)
    {
        if (!($node instanceof NestInterface)) {
            return;
        }
        if (null === $first = $node->getFirstChild()) {
            return;
        }
        if (!($first instanceof Tag)) {
            return;
        }
        return $first;
    }

    protected function getLastChildIfTag(NodeAbstract $node)
    {
        if (!($node instanceof NestInterface)) {
            return;
        }
        if (null === $last = $node->getLastChild()) {
            return;
        }
        if (!($last instanceof Tag)) {
            return;
        }
        return $last;
    }

    protected function getPreviousIfTag(NodeAbstract $node)
    {
        if (null === $tag = $node->getPreviousSibling()) {
            return;
        }
        if (!($tag instanceof Tag)) {
            return;
        }
        return $tag;
    }

    protected function getNextIfTag(NodeAbstract $node)
    {
        if (null === $tag = $node->getNextSibling()) {
            return;
        }
        if (!($tag instanceof Tag)) {
            return;
        }
        return $tag;
    }

    protected function shouldIndentBeforeOpen(NodeAbstract $node)
    {
        if (null !== $parent = $this->getParentIfFirstChild($node)) {
            if ($parent->getFlags() & Tag::FLAG_REMOVE_INNER_WHITESPACES) {
                return false;
            }
        }
        if ($node instanceof Tag) {
            if ($node->getFlags() & Tag::FLAG_REMOVE_OUTER_WHITESPACES) {
                return false;
            }
        }
        if (null !== $prev = $this->getPreviousIfTag($node)) {
            if ($prev->getFlags() & Tag::FLAG_REMOVE_OUTER_WHITESPACES) {
                return false;
            }
        }
        return true;
    }

    protected function shouldBreakAfterOpen(NodeAbstract $node)
    {
        if ($node instanceof Tag) {
            if ($node->getFlags() & Tag::FLAG_REMOVE_INNER_WHITESPACES) {
                return false;
            }
            if (!$node->hasChilds()) {
                return false;
            }
        }
        if (null !== $child = $this->getFirstChildIfTag($node)) {
            if ($child->getFlags() & Tag::FLAG_REMOVE_OUTER_WHITESPACES) {
                return false;
            }
        }

        return true;
    }

    protected function shouldIndentBeforeClose(NodeAbstract $node)
    {
        if ($node instanceof Tag) {
            if ($node->getFlags() & Tag::FLAG_REMOVE_INNER_WHITESPACES) {
                return false;
            }
            if (!$node->hasChilds()) {
                return false;
            }
        }
        if (null !== $child = $this->getLastChildIfTag($node)) {
            if ($child->getFlags() & Tag::FLAG_REMOVE_OUTER_WHITESPACES) {
                return false;
            }
        }
        return true;
    }

    protected function shouldBreakAfterClose(NodeAbstract $node)
    {
        if (null !== $parent = $this->getParentIfLastChild($node)) {
            if ($parent->getFlags() & Tag::FLAG_REMOVE_INNER_WHITESPACES) {
                return false;
            }
        }
        if (null !== $next = $this->getNextIfTag($node)) {
            if ($next->getFlags() & Tag::FLAG_REMOVE_OUTER_WHITESPACES) {
                return false;
            }
        }
        if ($node instanceof Tag) {
            if ($node->getFlags() & Tag::FLAG_REMOVE_OUTER_WHITESPACES) {
                return false;
            }
        }
        return true;
    }

    public function setEchoMode($enabled)
    {
        $this->echoMode = $enabled;
    }

    public function isEchoMode()
    {
        return $this->echoMode;
    }
}

