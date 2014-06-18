<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Root;
use MtHaml\Node\Tag;
use MtHaml\Node\TagAttribute;
use MtHaml\Node\Statement;
use MtHaml\Node\Text;
use MtHaml\Node\Insert;
use MtHaml\Node\Run;
use MtHaml\Node\InterpolatedString;
use MtHaml\Node\Comment;
use MtHaml\Node\Doctype;
use MtHaml\Node\Filter;
use MtHaml\Node\ObjectRefClass;
use MtHaml\Node\ObjectRefId;

class Printer extends NodeVisitorAbstract
{
    protected $indent;
    protected $output = '';

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
        if ($break) {
            $this->output .= "\n";
        }

        return $this;
    }

    protected function raw($string)
    {
        $this->output .= $string;

        return $this;
    }

    protected function writeIndentation()
    {
        $this->output .= str_repeat(' ', $this->indent * 2);

        return $this;
    }

    public function enterRoot(Root $node)
    {
        $this->write('root(')->indent();
    }
    public function leaveRoot(Root $node)
    {
        $this->undent()->write(')');
    }

    public function enterTag(Tag $node)
    {
        $name = $node->getTagName();
        $flags = $node->getFlags();
        if ($flags & Tag::FLAG_REMOVE_INNER_WHITESPACES) {
            $name .= '<';
        }
        if ($flags & Tag::FLAG_REMOVE_OUTER_WHITESPACES) {
            $name .= '>';
        }
        if ($flags & Tag::FLAG_SELF_CLOSE) {
            $name .= '/';
        }

        $this->write('tag(' . $name, true, false)
            ->indent();

        if ($node->hasContent()) {
            $this->raw(' ');
            $node->getContent()->accept($this);
        }

        if ($node->hasAttributes() || $node->hasChilds()) {
            $this->raw("\n");
        }
    }

    public function enterTagContent(Tag $node)
    {
        return false;
    }

    public function leaveTag(Tag $node)
    {
        $this->undent()->write(')', $node->hasAttributes()||$node->hasChilds());
    }

    public function enterTagAttribute(TagAttribute $node)
    {
        $this->write('attr(', true, false);
    }

    public function leaveTagAttribute(TagAttribute $node)
    {
        $this->write(')', false, true);
    }

    public function enterStatement(Statement $node)
    {
        $this->write('', true, false);
    }

    public function leaveStatement(Statement $node)
    {
        $this->write('', false, true);
    }

    public function enterText(Text $node)
    {
        $content = $node->getContent();

        $escaping = $node->getEscaping();
        if (true === $escaping->isEnabled()) {
            $flag = '&';
            if ($node->getEscaping()->isOnce()) {
                $flag .= '!';
            }
            $content = $flag . $content;
        } elseif (false === $escaping->isEnabled()) {
            $content = '!' . $content;
        }

        $this->raw('text('.$content.')');
    }

    public function enterInsert(Insert $node)
    {
        $content = $node->getContent();

        $escaping = $node->getEscaping();
        if (true === $escaping->isEnabled()) {
            $flag = '&';
            if ($node->getEscaping()->isOnce()) {
                $flag .= '!';
            }
            $content = $flag . $content;
        } elseif (false === $escaping->isEnabled()) {
            $content = '!' . $content;
        }

        $this->raw('insert('.$content.')');
    }

    public function enterRun(Run $node)
    {
        $this->write('run(' . $node->getContent(), true, $node->hasChilds())
            ->indent();
    }

    public function enterRunMidblock(Run $node)
    {
        if ($node->hasMidblock()) {
            $this->write('midblock(')->indent();
        }
    }

    public function leaveRunMidblock(Run $node)
    {
        if ($node->hasMidblock()) {
            $this->undent()->write(')');
        }
    }

    public function leaveRun(Run $node)
    {
        $this->undent()->write(')', $node->hasChilds());
    }

    public function enterInterpolatedString(InterpolatedString $node)
    {
        $this->raw('interpolated(');
    }

    public function leaveInterpolatedString(InterpolatedString $node)
    {
        $this->raw(')');
    }

    public function enterComment(Comment $node)
    {
        $this->write('comment(' . $node->getCondition(), true, false)->indent();
    }

    public function enterCommentChilds(Comment $node)
    {
        if ($node->hasChilds()) {
            $this->raw("\n");
        }
    }

    public function leaveComment(Comment $node)
    {
        $this->undent()->write(')', $node->hasChilds());
    }

    public function enterDoctype(Doctype $doctype)
    {
        $str = 'doctype(';
        $str .= $doctype->getDoctypeId() ?: 'default';
        if ($options = $doctype->getOptions()) {
            $str .= ', ' . $options;
        }
        $str .= ')';
        $this->write($str);
    }

    public function enterFilter(Filter $node)
    {
       $this->write('filter(' . $node->getFilter())->indent();
    }

    public function leaveFilter(Filter $node)
    {
        $this->undent()->write(')');
    }

    public function enterObjectRefClass(ObjectRefClass $node)
    {
        $this->raw('object_ref_class(');
    }

    public function leaveObjectRefClass(ObjectRefClass $node)
    {
        $this->raw(')');
    }

    public function enterObjectRefId(ObjectRefId $node)
    {
        $this->raw('object_ref_id(');
    }

    public function leaveObjectRefId(ObjectRefId $node)
    {
        $this->raw(')');
    }

}
