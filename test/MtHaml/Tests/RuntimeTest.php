<?php

namespace MtHaml\Tests {

use MtHaml\Runtime;
use MtHaml\Runtime\AttributeList;

require_once __DIR__ . '/TestCase.php';

class RuntimeTest extends TestCase
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
            'deeply nested data attribute' => array(
                'foo="bar" data-a="A" data-b="B" data-c-d-e-f="F" data-c-g="G"',
                array(
                    array('foo', 'bar'),
                    array('data', array(
                        'a' => 'A',
                        'b' => 'B',
                        'c' => array(
                            'd' => array(
                                'e' => array(
                                    'f' => 'F',
                                ),
                            ),
                            'g' => 'G',
                        ),
                    )),
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
            'attribute list' => array(
                'foo="bar" bar="baz" baz="qux" all="ok"',
                array(
                    array('foo', 'bar'),
                    AttributeList::create(array(
                        'bar' => 'baz',
                        'baz' => 'qux',
                    )),
                    array('all', 'ok'),
                ),
            ),
            'attribute list are properly merged' => array(
                'class="foo bar" id="x_43" all="ok"',
                array(
                    array('class', 'foo'),
                    array('id', 'x'),
                    AttributeList::create(array(
                        'class' => 'bar',
                        'id' => '43',
                    )),
                    array('all', 'ok'),
                ),
            ),
        );
    }

    /**
     * @dataProvider getObjectRefClassStringData
     */
    public function testGetObjectRefClassStringData($expect, $class)
    {
        $result = Runtime::getObjectRefClassString(new $class);
        $this->assertSame($expect, $result);
    }

    public function getObjectRefClassStringData()
    {
        return array(
            'simple' => array('foo_bar', 'FooBar'),
            'underscores in name' => array('foo_bar', 'Foo_Bar'),
            'multiple upper case' => array('foo_bbar', 'FooBBar'),
            'namespace' => array('baz_qux', 'Foo\Bar\BazQux'),
        );
    }

    public function testRenderObjectRefClass()
    {
        $object = new \stdClass;
        $result = Runtime::renderObjectRefClass($object);
        $this->assertSame('std_class', $result);

        $object = new \stdClass;
        $result = Runtime::renderObjectRefClass($object, 'pref<');
        $this->assertSame('pref<_std_class', $result);
    }

    public function testRenderObjectRefId()
    {
        $object = new ObjectRefWithGetIdAndId;
        $result = Runtime::renderObjectRefId($object);
        $this->assertSame('object_ref_with_get_id_and_id_>get_id', $result);

        $object = new ObjectRefWithGetIdAndId;
        $result = Runtime::renderObjectRefId($object, 'pref<');
        $this->assertSame('pref<_object_ref_with_get_id_and_id_>get_id', $result);

        $object = new ObjectRefWithId;
        $result = Runtime::renderObjectRefId($object);
        $this->assertSame('object_ref_with_id_>id', $result);

        $object = new ObjectRefWithGetIdAndId;
        $object->getId = null;
        $result = Runtime::renderObjectRefId($object);
        $this->assertSame('object_ref_with_get_id_and_id_new', $result);
    }

    public function testRenderObjectRefWithRefMethod()
    {
        $object = new ObjectRefWithRefAndId;
        $result = Runtime::getObjectRefName($object);
        $this->assertSame('customRef', $result);

        $result = Runtime::renderObjectRefId($object);
        $this->assertSame('custom_ref_>id', $result);
    }

    /** @dataProvider getRuntimeTests */
    public function testRuntime($file)
    {
        $parts = $this->parseTestFile($file);

        file_put_contents($file . '.haml', $parts['HAML']);
        file_put_contents($file . '.php', $parts['FILE']);
        file_put_contents($file . '.exp', $parts['EXPECT']);

        try {
            ob_start();
            require $file . '.php';
            $out = ob_get_clean();
        } catch (\Exception $e) {
            $this->assertException($parts, $e);
            $this->cleanup($file);

            return;
        }
        $this->assertException($parts);

        file_put_contents($file . '.out', $out);

        $this->assertSame($parts['EXPECT'], $out);

        $this->cleanup($file);
    }

    protected function cleanup($file)
    {
        if (file_exists($file . '.out')) {
            unlink($file . '.out');
        }
        unlink($file . '.haml');
        unlink($file . '.php');
        unlink($file . '.exp');
    }

    public function getRuntimeTests()
    {
        if (false !== $tests = getenv('ENV_TESTS')) {
            $files = explode(' ', $tests);
        } else {
            $files = glob(__DIR__ . '/fixtures/runtime/*.test');
        }

        return array_map(function ($file) {
                return array($file);
            }, $files);
    }
}

class ObjectRefWithGetIdAndId
{
    public $getId = '>get_id';
    public $id = '>id';

    public function getId()
    {
        return $this->getId;
    }

    public function id()
    {
        return $this->id;
    }
}

class ObjectRefWithId
{
    public function id()
    {
        return '>id';
    }
}

class ObjectRefWithRefAndId extends ObjectRefWithId
{
    public function hamlObjectRef()
    {
        return 'customRef';
    }
}

class ObjectRefWithoutId
{
    protected function id()
    {
        return '>id';
    }
}
}
namespace {
    class Foo_Bar {}
    class FooBar {}
    class FooBBar {}
}

namespace Foo\Bar {
    class BazQux {}
}
