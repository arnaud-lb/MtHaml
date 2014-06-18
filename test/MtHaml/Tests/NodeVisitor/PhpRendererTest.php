<?php

namespace MtHaml\Tests\NodeVisitor;

use MtHaml\NodeVisitor\PhpRenderer;
use MtHaml\Environment;

class PhpRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestTrimInlineCommentsData
     */
    public function testTrimInlineComments($expect, $code)
    {
        $env = new Environment('php');
        $renderer = new PhpRenderer($env);
        $result = $renderer->trimInlineComments($code);
        $this->assertSame($expect, $result);
    }

    public function getTestTrimInlineCommentsData()
    {
        return array(
            'no comments' => array('1 + 2', '1 + 2'),
            '# comment' => array('1 + 2', '1 + 2 # comment'),
            '// comment' => array('1 + 2', '1 + 2 // comment'),
            'comment without whitespace' => array('1 + 2', '1 + 2// comment'),

            'double quoted string' => array(
                '"foo"', '"foo" # bar'
            ),
            'double quoted string with escapes' => array(
                '"f\\\\o\\"o\n"', '"f\\\\o\\"o\n" # bar'
            ),
            'single quoted string' => array(
                '\'foo\'', '\'foo\' # bar'
            ),
            'single quoted string with escapes' => array(
                '\'f\\\\o\\\'o\'', '\'f\\\\o\\\'o\' # bar'
            ),
            'backticks string' => array(
                '`foo`', '`foo` # bar'
            ),
            'backticks string with escapes' => array(
                '`f\\\\o\\`o`', '`f\\\\o\\`o` # bar'
            ),
            'double quoted string with #' => array(
                '"fo#o"', '"fo#o" # bar'
            ),
            '# in comment' => array(
                '"foo"', '"foo" # b # a # r'
            ),
        );
    }
}
