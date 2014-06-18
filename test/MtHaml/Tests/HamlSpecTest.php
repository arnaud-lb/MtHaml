<?php

namespace MtHaml\Tests;

use MtHaml\Environment;
use MtHaml\Support\Twig\Extension;

class HamlSpecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testSpec($name, $test)
    {
        if (!getenv('HAML_SPEC_TEST_JSON_PATH')) {
            $this->markTestSkipped('HAML_SPEC_TEST_JSON_PATH not set');
        }

        $config = array(
            'enable_escaper' => false,
        );

        if (isset($test['config'])) {
            foreach ($test['config'] as $key => $value) {
                switch ($key) {
                case 'format':
                    $config['format'] = $value;
                    break;
                default:
                }
            }
        }

        $locals = array();

        if (isset($test['locals'])) {
            $locals = $test['locals'];
        }

        $env = new Environment('twig', $config);
        $str = $env->compileString($test['haml'], "$name.haml");

        $loader = new \Twig_Loader_Array(array(
            'test.twig' => $str,
        ));
        $twig = new \Twig_Environment($loader);
        $twig->addExtension(new Extension);

        $html = $twig->render('test.twig', $locals);

        $expect = $test['html'];

        $this->assertSame($expect, rtrim($html));
    }

    public function getTestData()
    {
        $inputPath = getenv('HAML_SPEC_TEST_JSON_PATH');
        if (!$inputPath) {
            return array(array(null, null));
        }

        $input = json_decode(file_get_contents($inputPath), true);

        return $this->genData($input);
    }

    private function genData($input, $prefix = '')
    {
        $data = array();

        foreach ($input as $key => $value) {
            if (!isset($value['haml'])) {
                $data = array_merge($data, $this->genData($value, $prefix.$key.': '));
            } else {
                $name = $prefix.$key.': ';
                $data[$name] = array($name, $value);
            }
        }

        return $data;
    }
}
