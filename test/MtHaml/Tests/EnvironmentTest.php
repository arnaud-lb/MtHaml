<?php

namespace MtHaml\Tests;

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

        if (isset($parts['SKIPIF'])) {
            file_put_contents($file . '.skip.php', $parts['SKIPIF']);
        }

        if (isset($parts['SKIPIF'])) {
            ob_start();
            require $file . '.skip.php';
            $out = ob_get_clean();
            if (false !== strpos($out, 'skip')) {
                return $this->markTestSkipped();
            }
        }

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

        if(isset($parts['EVAL'])) {
            ob_start();
            eval('?>' . $out);
            $eval = ob_get_clean();
            $this->assertSame($parts['EVAL'], $eval);
        }

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

    public function getEnvironmentTests()
    {
        if (false !== $tests = getenv('ENV_TESTS')) {
            $files = explode(' ', $tests);
        } else {
            $files = glob(__DIR__ . '/fixtures/environment/*.test');
        }

        return array_map(function ($file) {
            return array($file);
        }, array_combine($files, $files));
    }
}
