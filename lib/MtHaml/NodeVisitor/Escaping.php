<?php

namespace MtHaml\NodeVisitor;

use MtHaml\Node\Text;
use MtHaml\Node\Insert;
use MtHaml\Node\TagAttribute;
use MtHaml\Node\NodeAbstract;
use MtHaml\Node\InterpolatedString;

class Escaping extends NodeVisitorAbstract
{
    /** do not auto-escape */
    const ESCAPE_FALSE = 1;

    /** do auto-escape */
    const ESCAPE_TRUE = 2;

    /** do auto-escape, but do not double-escape entities (haml compat) */
    const ESCAPE_ONCE = 3;

    protected $escapeHtml;
    protected $escapeAttrs;

    protected $inAttr = 0;
    protected $inInterpolatedString = 0;

    public function __construct($escapeHtml = self::ESCAPE_TRUE, $escapeAttrs = self::ESCAPE_TRUE)
    {
        $this->escapeHtml = $escapeHtml;
        $this->escapeAttrs = $escapeAttrs;
    }

    protected function escape(NodeAbstract $node)
    {
        $enabled = $node->getEscaping()->isEnabled();

        if ($enabled !== null) {
            return;
        }

        if ($node instanceof Text) {
            // interpolated strings that are not in attribute name/value
            // are plain HTML and should not be escaped
            if ($this->inInterpolatedString && !$this->inAttr) {
                $node->getEscaping()->setEnabled(false);
                return;
            }
        }

        // everything we don't explicitly not escape is escaped

        if ($this->inAttr) {
            $this->setEscape($node, $this->escapeAttrs);
        } else {
            $this->setEscape($node, $this->escapeHtml);
        }
    }

    protected function setEscape(NodeAbstract $node, $mode)
    {
        switch($mode) {
        case self::ESCAPE_FALSE:
            $node->getEscaping()->setEnabled(false);
            break;
        case self::ESCAPE_ONCE:
            $node->getEscaping()->setEnabled(true)->setOnce(true);
            break;
        case self::ESCAPE_TRUE:
            $node->getEscaping()->setEnabled(true);
            break;
        }
    }

    public function enterTagAttribute(TagAttribute $node)
    {
        ++$this->inAttr;
    }

    public function leaveTagAttribute(TagAttribute $node)
    {
        --$this->inAttr;
    }

    public function enterInterpolatedString(InterpolatedString $node)
    {
        ++$this->inInterpolatedString;
    }

    public function leaveInterpolatedString(InterpolatedString $node)
    {
        --$this->inInterpolatedString;
    }

    public function enterText(Text $node)
    {
        $this->escape($node);
    }

    public function enterInsert(Insert $node)
    {
        $this->escape($node);
    }
}

