<?php

namespace MtHaml\Tests\NodeVisitor;

use MtHaml\Node\Text;
use MtHaml\NodeVisitor\TwigRenderer;
use MtHaml\Node\InterpolatedString;

class TwigRendererTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider getCurlyPercentAndCurlyCurclyAreEscapedData */
    public function testCurlyPercentAndCurlyCurclyAreEscaped($expect, $node)
    {
        $env = $this->getMockBuilder('MtHaml\Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $r = new TwigRenderer($env);

        $node = $node();
        $node->accept($r);

        $output = $r->getOutput();
        $this->assertSame($expect, $output);
    }

    public function getCurlyPercentAndCurlyCurclyAreEscapedData()
    {
        $pos = array(0, 0);

        return array(
            'middle' => array(
                'expect' => "foo {{ '{{' }} bar {{ '{%' }} baz",
                'node' => function () use ($pos) {
                    return new Text($pos, "foo {{ bar {% baz");
                },
            ),
            'leading in node, not preceeded by {' => array(
                'expect' => "foo % bar { baz",
                'nodes' => function () use ($pos) {
                    return new InterpolatedString($pos, array(
                        new Text($pos, 'foo '),
                        new Text($pos, '% bar '),
                        new Text($pos, '{ baz'),
                    ));
                },
            ),
            'leading in node, preceeded by {' => array(
                'expect' => "foo {{{ '%' }} bar {{{ '{' }} baz",
                'nodes' => function () use ($pos) {
                    return new InterpolatedString($pos, array(
                        new Text($pos, 'foo {'),
                        new Text($pos, '% bar {'),
                        new Text($pos, '{ baz'),
                    ));
                },
            ),
            'leading in node, globally leading' => array(
                'expect' => "{{ '%' }} bar",
                'nodes' => function () use ($pos) {
                    return new InterpolatedString($pos, array(
                        new Text($pos, '% bar'),
                    ));
                },
            ),
        );
    }
}
