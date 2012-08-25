<?php

namespace MtHaml\Tests;

use MtHaml\Runtime;

class RuntimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestRenderAttributesData
     */
    public function testRenderAttributes($expect, $list, $format = 'html5', $charset = 'utf-8')
    {
        $result = Runtime::renderAttributes($list, $format, $charset);
        $this->assertSame($expect, $result);
    }

    public function getTestRenderAttributesData()
    {
        return array(
            'simple' => array(
                'foo="bar" bar="baz"',
                array(
                    array('foo', 'bar'),
                    array('bar', 'baz'),
                ),
            ),
            'duplicate attribute' => array(
                'bar="baz" foo="qux"',
                array(
                    array('foo', 'bar'),
                    array('bar', 'baz'),
                    array('foo', 'qux'),
                ),
            ),
            'data attribute' => array(
                'foo="bar" data-a="A" data-b="B"',
                array(
                    array('foo', 'bar'),
                    array('data', array('a' => 'A', 'b' => 'B')),
                ),
            ),
            'previous data attribute overridden by specific data- attribute' => array(
                'foo="bar" data-b="B" data-a="A2"',
                array(
                    array('foo', 'bar'),
                    array('data', array('a' => 'A', 'b' => 'B')),
                    array('data-a', 'A2'),
                ),
            ),
            'previous data- attribute not overridden by data attribute list' => array(
                'foo="bar" data-a="A2" data-b="B"',
                array(
                    array('foo', 'bar'),
                    array('data-a', 'A2'),
                    array('data', array('a' => 'A', 'b' => 'B')),
                ),
            ),
            'single id attribute' => array(
                'id="a"',
                array(
                    array('id', 'a'),
                ),
            ),
            'multiple id attributes are joined with _' => array(
                'id="a_b"',
                array(
                    array('id', 'a'),
                    array('id', 'b'),
                ),
            ),
            'multiple id attributes skip nulls and falses' => array(
                'id="a_b"',
                array(
                    array('id', 'a'),
                    array('id', null),
                    array('id', false),
                    array('id', 'b'),
                ),
            ),
            'id attributes recurse' => array(
                'id="a_b_c_d_e_f"',
                array(
                    array('id', 'a'),
                    array('id', array('b', null, 'c')),
                    array('id', array('d', array('e', 'f'))),
                ),
            ),
            'single class attribute' => array(
                'class="a"',
                array(
                    array('class', 'a'),
                ),
            ),
            'multiple class attributes are joined with _' => array(
                'class="a b"',
                array(
                    array('class', 'a'),
                    array('class', 'b'),
                ),
            ),
            'multiple class attributes skip nulls and falses' => array(
                'class="a b"',
                array(
                    array('class', 'a'),
                    array('class', null),
                    array('class', false),
                    array('class', 'b'),
                ),
            ),
            'class attributes recurse' => array(
                'class="a b c d e f"',
                array(
                    array('class', 'a'),
                    array('class', array('b', null, 'c')),
                    array('class', array('d', array('e', 'f'))),
                ),
            ),
            'boolean attributes are rendered without value in html5 format' => array(
                'foo',
                array(
                    array('foo', true),
                ),
            ),
            'boolean attributes are rendered with value in xhtml format' => array(
                'foo="foo"',
                array(
                    array('foo', true),
                ),
                'xhtml',
            ),
            'false and null attributes are not rendered' => array(
                null,
                array(
                    array('foo', null),
                    array('bar', false),
                ),
            ),
            'everything is escaped' => array(
                'foo&gt;="bar&gt;" data-foo&gt;="bar&gt;" data-bar&gt;="bar&gt;" id="bar&gt;" class="bar&gt;"',
                array(
                    array('foo>', 'bar>'),
                    array('data', array('foo>' => 'bar>')),
                    array('data-bar>', 'bar>'),
                    array('id', array('bar>')),
                    array('class', array('bar>')),
                ),
            ),
        );
    }
}
