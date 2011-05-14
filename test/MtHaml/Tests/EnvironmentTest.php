<?php

namespace MtHaml\Tests;

use MtHaml\Parser;
use MtHaml\NodeVisitor\Printer;

require_once __DIR__ . '/TestCase.php';

class EnvironmentTest extends TestCase
{
    /** @dataProvider getEnvironmentTests */
    public function testEnvironment($file)
    {
        $parts = $this->parseTestFile($file);

        file_put_contents($file . '.haml', $parts['HAML']);
        file_put_contents($file . '.php', $parts['FILE']);
        file_put_contents($file . '.exp', $parts['EXPECT']);

        try {
            ob_start();
            require $file . '.php';
            $out = ob_get_clean();
        } catch(\Exception $e) {
            return $this->assertException($parts, $e);
        }
        $this->assertException($parts);

        file_put_contents($file . '.out', $out);

        $this->assertSame($parts['EXPECT'], $out);

        unlink($file . '.out');
        unlink($file . '.haml');
        unlink($file . '.php');
        unlink($file . '.exp');
    }

    public function getEnvironmentTests()
    {
        if (false !== $tests = getenv('ENV_TESTS')) {
            $files = explode(' ', $tests);
        } else {
            $files = glob(__DIR__ . '/fixtures/environment/*.test');
        }
        return array_map(function($file) {
            return array($file);
        }, $files);
    }
}


