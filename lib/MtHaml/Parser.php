<?php

namespace MtHaml;

use MtHaml\Exception\SyntaxErrorException;
use MtHaml\Node\NodeAbstract;
use MtHaml\Parser\Buffer;
use MtHaml\Node\Doctype;
use MtHaml\Node\Tag;
use MtHaml\Node\TagAttribute;
use MtHaml\Node\Comment;
use MtHaml\Node\Insert;
use MtHaml\Node\Text;
use MtHaml\Node\InterpolatedString;
use MtHaml\Node\Run;
use MtHaml\Node\Statement;
use MtHaml\Node\Filter;
use MtHaml\Node\ObjectRefClass;
use MtHaml\Node\ObjectRefId;
use MtHaml\Node\TagAttributeInterpolation;
use MtHaml\Node\TagAttributeList;
use MtHaml\Indentation\IndentationException;
use MtHaml\TreeBuilder;

/**
 * MtHaml Parser
 */
class Parser
{
    protected $filename;
    protected $column;
    protected $lineno;

    /**
     * @var \MtHaml\Indentation\IndentationInterface
     */
    private $prevIndent;

    /**
     * @var \MtHaml\Indentation\IndentationInterface
     */
    private $indent;

    /**
     * @var \MtHaml\TreeBuilder
     */
    private $treeBuilder;

    public function __construct()
    {
        $this->treeBuilder = new TreeBuilder;
        $this->indent = new Indentation\Undefined();
        $this->prevIndent = $this->indent;
    }

    /**
     * Updates the indentation state
     *
     * @param Buffer $buf
     * @param string $indent The indentation characters of the current line
     */
    private function updateIndent(Buffer $buf, $indent)
    {
        $this->prevIndent = $this->indent;

        try {
            $this->indent = $this->indent->newLevel($indent);
        } catch (IndentationException $e) {
            throw $this->syntaxError($buf, $e->getMessage());
        }

        if (!$this->treeBuilder->hasStatements() && 0 < $this->indent->getLevel()) {
            throw $this->syntaxError($buf, 'Indenting at the beginning of the document is illegal');
        }
    }

    /**
     * Processes a statement
     *
     * Inserts a new $node in the tree
     *
     * @param Buffer       $buf
     * @param NodeAbstract $node Node to insert in the tree
     */
    public function processStatement(Buffer $buf, NodeAbstract $node)
    {
        $level = $this->indent->getLevel() - $this->prevIndent->getLevel();

        try {
            $this->treeBuilder->addChild($level, $node);
        } catch(TreeBuilderException $e) {
            throw $this->syntaxError($buf, $e->getMessage());
        }
    }

    /**
     * Parses a HAML document
     *
     * @param string $string    A HAML document
     * @param string $fileaname Filename to report in error messages
     * @param string $lineno    Line number of the first line of $string in
     *                          $filename (for error messages)
     */
    public function parse($string, $filename, $lineno = 1)
    {
        $this->filename = $filename;

        $buf = new Buffer($string, $lineno);
        while ($buf->nextLine()) {
            $this->handleMultiline($buf);
            $this->parseLine($buf);
        }

        return $this->treeBuilder->getRoot();
    }

    /**
     * Handles HAML multiline syntax
     *
     * Any line terminated by ` |` is concatenated with the following lines
     * also terminated by ` |`. Empty or whitespace-only lines are ignored. The
     * current line is replaced by the resulting line in $buf.
     *
     * @param Buffer $buf
     */
    public function handleMultiline(Buffer $buf)
    {
        $line = $buf->getLine();

        if (!$this->isMultiline($line)) {
            return;
        }

        $line = substr(rtrim($line), 0, -1);

        while ($next = $buf->peekLine()) {
            if (trim($next) == '') {
                $buf->nextLine();
                continue;
            }
            if (!$this->isMultiline($next)) break;
            $line .= substr(trim($next), 0, -1);
            $buf->nextLine();
        }

        $buf->replaceLine($line);
    }

    public function isMultiline($string)
    {
        return ' |' === substr(rtrim($string), -2);
    }

    /**
     * Parses a HAML line
     */
    protected function parseLine(Buffer $buf)
    {
        if ('' === trim($buf->getLine())) {
            return;
        }

        $buf->match('/[ \t]*/A', $match);
        $indent = $match[0];
        $this->updateIndent($buf, $indent);

        if (null === $node = $this->parseStatement($buf)) {
            throw $this->syntaxErrorExpected($buf, 'statement');
        }
        $this->processStatement($buf, $node);
    }

    protected function parseStatement(Buffer $buf)
    {
        if (null !== $node = $this->parseTag($buf)) {
            return $node;

        } elseif (null !== $node = $this->parseFilter($buf)) {
            return $node;

        } elseif (null !== $comment = $this->parseComment($buf)) {
            return $comment;

        } else if (null !== $run = $this->parseRun($buf)) {
            return $run;

        } elseif (null !== $doctype = $this->parseDoctype($buf)) {
            return $doctype;

        } else {
            if (null !== $node = $this->parseNestableStatement($buf)) {
                return new Statement($node->getPosition(), $node);
            }
        }
    }

    protected function parseDoctype(Buffer $buf)
    {
        $doctypeRegex = '/
            !!!                         # start of doctype decl
            (?:
                \s(?P<type>[^\s]+)      # optional doctype id
                (?:\s(?P<options>.*))?  # doctype options (e.g. charset, for
                                        # xml decls)
            )?$/Ax';

        if ($buf->match($doctypeRegex, $match)) {

            $type = empty($match['type']) ? null : $match['type'];
            $options = empty($match['options']) ? null : $match['options'];
            $node = new Doctype($match['pos'][0], $type, $options);

            return $node;
        }
    }

    protected function parseComment(Buffer $buf)
    {
        if ($buf->match('!(-#|/)\s*!A', $match)) {
            $pos = $match['pos'][0];
            $rendered = '/' === $match[1];
            $condition = null;

            if ($rendered) {
                // IE conditional comments
                // example: [if IE lte 8]
                //
                // matches nested [...]
                if ($buf->match('!(\[ ( [^\[\]]+ | (?1) )+  \])$!Ax', $match)) {
                    $condition = $match[0];
                }
            }

            $node = new Comment($pos, $rendered, $condition);

            if ('' !== $line = trim($buf->getLine())) {
                $content = new Text($buf->getPosition(), $line);
                $node->setContent($content);
            }

            if (!$rendered) {

                while (null !== $next = $buf->peekLine()) {

                    $indent = '';

                    if ('' !== trim($next)) {
                        $indent = $this->indent->getString(1, $next);
                        if ('' === $indent) {
                            break;
                        }
                        if (strpos($next, $indent) !== 0) {
                            break;
                        }
                    }

                    $buf->nextLine();

                    if ('' !== trim($next)) {
                        $buf->eatChars(strlen($indent));
                        $str = new Text($buf->getPosition(), $buf->getLine());
                        $node->addChild(new Statement($str->getPosition(), $str));
                    }
                }
            }

            return $node;
        }
    }

    protected function getMultilineCode(Buffer $buf)
    {
        $code = $buf->getLine();
        while (preg_match('/,\s*$/', $code)) {
            $buf->nextLine();
            $line = trim($buf->getLine());
            if ('' !== $line) {
                $code .= ' ' . $line;
            }
        }
        return $code;
    }

    protected function parseRun(Buffer $buf)
    {
        if ($buf->match('/-(?!#)/A', $match)) {
            $buf->skipWs();
            $code = $this->getMultilineCode($buf);
            return new Run($match['pos'][0], $code);
        }
    }

    protected function parseTag(Buffer $buf)
    {
        $tagRegex = '/
            %(?P<tag_name>[\w:-]+)  # explicit tag name ( %tagname )
            | (?=[.#][\w-])         # implicit div followed by class or id
                                    # ( .class or #id )
            /xA';

        if ($buf->match($tagRegex, $match)) {
            $tag_name = empty($match['tag_name']) ? 'div' : $match['tag_name'];

            $attributes = $this->parseTagAttributes($buf);

            $flags = $this->parseTagFlags($buf);

            $node = new Tag($match['pos'][0], $tag_name, $attributes, $flags);

            $buf->skipWs();

            if (null !== $nested = $this->parseNestableStatement($buf)) {

                if ($flags & Tag::FLAG_SELF_CLOSE) {
                    $msg = 'Illegal nesting: nesting within a self-closing tag is illegal';
                    throw $this->syntaxError($buf, $msg);
                }

                $node->setContent($nested);
            }

            return $node;
        }
    }

    protected function parseTagFlags(Buffer $buf)
    {
        $flags = 0;
        while (null !== $char = $buf->peekChar()) {
            switch ($char) {
                case '<':
                    $flags |= Tag::FLAG_REMOVE_INNER_WHITESPACES;
                    $buf->eatChar();
                    break;
                case '>':
                    $flags |= Tag::FLAG_REMOVE_OUTER_WHITESPACES;
                    $buf->eatChar();
                    break;
                case '/':
                    $flags |= Tag::FLAG_SELF_CLOSE;
                    $buf->eatChar();
                    break;
                default:
                    break 2;
            }
        }

        return $flags;
    }

    protected function parseTagAttributes(Buffer $buf)
    {
        $attrs = array();

        // short notation for classes and ids

        while ($buf->match('/(?P<type>[#.])(?P<name>[\w-]+)/A', $match)) {
            if ($match['type'] == '#') {
                $name = 'id';
            } else {
                $name = 'class';
            }
            $name = new Text($match['pos'][0], $name);
            $value = new Text($match['pos'][1], $match['name']);
            $attr = new TagAttribute($match['pos'][0], $name, $value);
            $attrs[] = $attr;
        }

        $hasRubyAttrs = false;
        $hasHtmlAttrs = false;
        $hasObjectRef = false;

        // accept ruby-attrs, html-attrs, and object-ref in any order,
        // but only one of each

        while (true) {
            switch ($buf->peekChar()) {
            case '{':
                if ($hasRubyAttrs) {
                    break 2;
                }
                $hasRubyAttrs = true;
                $newAttrs = $this->parseTagAttributesRuby($buf);
                $attrs = array_merge($attrs, $newAttrs);
                break;
            case '(':
                if ($hasHtmlAttrs) {
                    break 2;
                }
                $hasHtmlAttrs = true;
                $newAttrs = $this->parseTagAttributesHtml($buf);
                $attrs = array_merge($attrs, $newAttrs);
                break;
            case '[':
                if ($hasObjectRef) {
                    break 2;
                }
                $hasObjectRef = true;
                $newAttrs = $this->parseTagAttributesObject($buf);
                $attrs = array_merge($attrs, $newAttrs);
                break;
            default:
                break 2;
            }
        }

        return $attrs;
    }

    protected function parseTagAttributesRuby(Buffer $buf)
    {
        $attrs = array();

        if ($buf->match('/\{\s*/')) {
            do {
                $attrs[] = $this->parseTagAttributeRuby($buf);

                $buf->skipWs();

                if ($buf->match('/}/A')) {
                    break;
                }

                $buf->skipWs();
                if (!$buf->match('/,\s*/A')) {
                    throw $this->syntaxErrorExpected($buf, "',' or '}'");
                }
                // allow line break after comma
                if ($buf->isEol()) {
                    $buf->nextLine();
                    $buf->skipWs();
                }
            } while (true);
        }

        return $attrs;
    }

    protected function parseTagAttributeRuby(Buffer $buf)
    {
        if ($expr = $this->parseInterpolation($buf)) {
            return new TagAttributeInterpolation($expr->getPosition(), $expr);
        }

        list ($name, $ruby19) = $this->parseTagAttributeNameRuby($buf);

        $buf->skipWs();

        if (!$ruby19 && !$buf->match('/=>\s*/A')) {
            return new TagAttributeList($name->getPosition(), $name);
        }

        $value = $this->parseTagAttributeValueRuby($buf);

        return new TagAttribute($name->getPosition(), $name, $value);
    }

    protected function parseTagAttributeNameRuby(Buffer $buf)
    {
        try {
            if ($name = $this->parseTagAttributeNameRuby19($buf)) {
                return array($name, true);
            }

            return array($this->parseAttrExpression($buf, '=,'), false);
        } catch (SyntaxErrorException $e) {
            // Allow line break after comma
            if ($buf->match('/,\s*$/', $match, false) && $buf->hasNextLine()) {
                $buf->mergeNextLine();
                return $this->parseTagAttributeNameRuby($buf);
            } else {
                throw $e;
            }
        }
    }

    protected function parseTagAttributeNameRuby19(Buffer $buf)
    {
        if ($buf->match('/(\w+):/A', $match)) {
            return new Text($match['pos'][0], $match[1]);
        }
    }

    protected function parseTagAttributeValueRuby(Buffer $buf)
    {
        try {
            return $this->parseAttrExpression($buf, ',');
        } catch (SyntaxErrorException $e) {
            // Allow line break after comma
            if ($buf->match('/,\s*$/', $match, false) && $buf->hasNextLine()) {
                $buf->mergeNextLine();
                return $this->parseTagAttributeValueRuby($buf);
            } else {
                throw $e;
            }
        }
    }

    protected function parseTagAttributesHtml(Buffer $buf)
    {
        if (!$buf->match('/\(\s*/A')) {
            return null;
        }

        $attrs = array();

        do {

            $attrs[] = $this->parseTagAttributeHtml($buf);

            if ($buf->match('/\s*\)/A')) {
                break;
            }

            if (!$buf->match('/\s+/A')) {
                if (!$buf->isEol()) {
                    throw $this->syntaxErrorExpected($buf, "' ', ')' or end of line");
                }
            }

            // allow line break
            if ($buf->isEol()) {
                $buf->nextLine();
                $buf->skipWs();
            }

        } while (true);

        return $attrs;
    }

    private function parseTagAttributeHtml(Buffer $buf)
    {
        if ($expr = $this->parseInterpolation($buf)) {
            return new TagAttributeInterpolation($expr->getPosition(), $expr);
        }

        if ($buf->match('/[\w+:-]+/A', $match)) {
            $name = new Text($match['pos'][0], $match[0]);

            if (!$buf->match('/\s*=\s*/A')) {
                $value = null;
            } else {
                $value = $this->parseAttrExpression($buf, ' ');
            }

            return new TagAttribute($name->getPosition(), $name, $value);
        }

        throw $this->syntaxErrorExpected($buf, 'html attribute name or #{interpolation}');
    }

    protected function parseTagAttributesObject(Buffer $buf)
    {
        $nodes = array();
        $attrs = array();

        if (!$buf->match('/\[\s*/A', $match)) {
            return $attrs;
        }

        $pos = $match['pos'][0];

        do {
            if ($buf->match('/\s*\]\s*/A')) {
                break;
            }

            list($expr, $pos) = $this->parseExpression($buf, ',\\]');
            $nodes[] = new Insert($pos, $expr);

            if ($buf->match('/\s*\]\s*/A')) {
                break;
            } elseif (!$buf->match('/\s*,\s*/A')) {
                throw $this->syntaxErrorExpected($buf, "',' or ']'");
            }

        } while (true);

        list ($object, $prefix) = array_pad($nodes, 2, null);

        if (!$object) {
            return $attrs;
        }

        $class = new ObjectRefClass($pos, $object, $prefix);
        $id = new ObjectRefId($pos, $object, $prefix);

        $name = new Text($pos, 'class');
        $attrs[] = new TagAttribute($pos, $name, $class);

        $name = new Text($pos, 'id');
        $attrs[] = new TagAttribute($pos, $name, $id);

        return $attrs;
    }

    protected function parseAttrExpression(Buffer $buf, $delims)
    {
        $sub = clone $buf;

        list($expr, $pos) = $this->parseExpression($buf, $delims);

        // hack to return a parsed string or symbol instead of an expression
        // if the whole expression can be parsed as string or symbol.

        if (preg_match('/"/A', $expr)) {
            try {
                $string = $this->parseInterpolatedString($sub);
                if ($sub->getColumn() >= $buf->getColumn()) {
                    $buf->eatChars($sub->getColumn() - $buf->getColumn());

                    return $string;
                }
            } catch (SyntaxErrorException $e) {
            }
        } elseif (preg_match('/:/A', $expr)) {
            try {
                $sym = $this->parseSymbol($sub);
                if ($sub->getColumn() >= $buf->getColumn()) {
                    $buf->eatChars($sub->getColumn() - $buf->getColumn());

                    return $sym;
                }
            } catch (SyntaxErrorException $e) {
            }
        }

        return new Insert($pos, $expr);
    }

    protected function parseExpression(Buffer $buf, $delims)
    {
        // matches everything until a delimiter is found
        // delimiters are allowed inside quoted strings,
        // {}, and () (recursive)

        $re = "/(?P<expr>(?:

                # anything except \", ', (), {}, []
                (?:[^(){}\[\]\"\'\\\\$delims]+(?=(?P>expr)))
                |(?:[^(){}\[\]\"\'\\\\ $delims]+)

                # double quoted string
                | \"(?: [^\"\\\\]+ | \\\\[\#\"\\\\] )*\"

                # single quoted string
                | '(?: [^'\\\\]+ | \\\\[\#'\\\\] )*'

                # { ... } pair
                | \{ (?: (?P>expr) | [ $delims] )* \}

                # ( ... ) pair
                | \( (?: (?P>expr) | [ $delims] )* \)

                # [ ... ] pair
                | \[ (?: (?P>expr) | [ $delims] )* \]
            )+)/xA";

        if ($buf->match($re, $match)) {
            return array($match[0], $match['pos'][0]);
        }

        throw $this->syntaxErrorExpected($buf, 'target language expression');
    }

    protected function parseSymbol(Buffer $buf)
    {
        if (!$buf->match('/:(\w+)/A', $match)) {
            throw $this->syntaxErrorExpected($buf, 'symbol');
        }

        return new Text($match['pos'][0], $match[1]);
    }

    protected function parseInterpolatedString(Buffer $buf, $quoted = true)
    {
        if ($quoted && !$buf->match('/"/A', $match)) {
            throw $this->syntaxErrorExpected($buf, 'double quoted string');
        }

        $node = new InterpolatedString($buf->getPosition());

        if ($quoted) {
            $stringRegex = '/(
                    [^\#"\\\\]+           # anything without hash or " or \
                    |\\\\(?:["\\\\]|\#\{) # or escaped quote slash or hash followed by {
                    |\#(?!\{)             # or hash, but not followed by {
                )+/Ax';
        } else {
            $stringRegex = '/(
                    [^\#\\\\]+          # anything without hash or \
                    |\\\\(?:\#\{|[^\#]) # or escaped hash followed by { or anything without hash
                    |\#(?!\{)           # or hash, but not followed by {
                )+/Ax';
        }

        do {
            if ($buf->match($stringRegex, $match)) {
                $text = $match[0];
                if ($quoted) {
                    // strip slashes
                    $text = preg_replace('/\\\\(["\\\\])/', '\\1', $match[0]);
                }
                // strip back slash before hash followed by {
                $text = preg_replace('/\\\\\#\{/', '#{', $text);
                $text = new Text($match['pos'][0], $text);
                $node->addChild($text);
            } elseif ($expr = $this->parseInterpolation($buf)) {
                $node->addChild($expr);
            } elseif ($quoted && $buf->match('/"/A')) {
                break;
            } elseif (!$quoted && $buf->match('/$/A')) {
                break;
            } else {
                throw $this->syntaxErrorExpected($buf, 'string or #{...}');
            }
        } while (true);

        // ensure that the InterpolatedString has at least one child
        if (0 === count($node->getChilds())) {
            $text = new Text($buf->getPosition(), '');
            $node->addChild($text);
        }

        return $node;
    }

    protected function parseInterpolation(Buffer $buf)
    {
        // This matches an interpolation:
        // #{ expr... }
        $exprRegex = '/
            \#\{(?P<insert>(?P<expr>
                # do not allow {}"\' in expr
                [^\{\}"\']+
                # allow balanced {}
                | \{ (?P>expr)* \}
                # allow balanced \'
                | \'([^\'\\\\]+|\\\\[\'\\\\])*\'
                # allow balanced "
                | "([^"\\\\]+|\\\\["\\\\])*"
            )+)\}
            /AxU';

        if ($buf->match($exprRegex, $match)) {
            return new Insert($match['pos']['insert'], $match['insert']);
        }
    }

    protected function parseNestableStatement(Buffer $buf)
    {
        if ($insert = $this->parseInsert($buf)) {
            return $insert;
        }

        if (null !== $comment = $this->parseComment($buf)) {
            return $comment;
        }

        if ('\\' === $buf->peekChar()) {
            $buf->eatChar();
        }

        if (strlen(trim($buf->getLine())) > 0) {
            return $this->parseInterpolatedString($buf, false);
        }
    }

    protected function parseInsert(Buffer $buf)
    {
        if ($buf->match('/([&!]?)(==?|~)\s*/A', $match)) {

            if ($match[2] == '==') {
                $node = $this->parseInterpolatedString($buf, false);
            } else {
                $code = $this->getMultilineCode($buf);
                $node = new Insert($match['pos'][0], $code);
            }

            if ($match[1] == '&') {
                $node->getEscaping()->setEnabled(true);
            } elseif ($match[1] == '!') {
                $node->getEscaping()->setEnabled(false);
            }

            $buf->skipWs();

            return $node;
        }
    }

    protected function parseFilter(Buffer $buf)
    {
        if (!$buf->match('/:(.*)/A', $match)) {
            return null;
        }

        $node = new Filter($match['pos'][0], $match[1]);

        while (null !== $next = $buf->peekLine()) {

            $indent = '';

            if ('' !== trim($next)) {
                $indent = $this->indent->getString(1, $next);
                if ('' === $indent) {
                    break;
                }
                if (strpos($next, $indent) !== 0) {
                    break;
                }
            }

            $buf->nextLine();
            $buf->eatChars(strlen($indent));
            $str = $this->parseInterpolatedString($buf, false);
            $node->addChild(new Statement($str->getPosition(), $str));
        }

        return $node;
    }

    protected function syntaxErrorExpected(Buffer $buf, $expected)
    {
        $unexpected = $buf->peekChar();
        if ($unexpected) {
            $unexpected = "'$unexpected'";
        } else {
            $unexpected = 'end of line';
        }
        $msg = sprintf("Unexpected %s, expected %s", $unexpected, $expected);
        return $this->syntaxError($buf, $msg);
    }

    protected function syntaxError(Buffer $buf, $msg)
    {
        $this->column = $buf->getColumn();
        $this->lineno = $buf->getLineno();

        $msg = sprintf('%s in %s on line %d, column %d',
            $msg, $this->filename, $this->lineno, $this->column);

        return new SyntaxErrorException($msg);
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getLineno()
    {
        return $this->lineno;
    }

    public function getFilename()
    {
        return $this->filename;
    }

}
