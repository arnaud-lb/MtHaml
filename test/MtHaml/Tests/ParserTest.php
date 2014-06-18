<?php

namespace MtHaml\Tests;

use MtHaml\Parser;
use MtHaml\NodeVisitor\Printer;

require_once __DIR__ . '/TestCase.php';

class ParserTest extends TestCase
{
    /** @dataProvider getParserFixtures */
    public function testParser($file)
    {
        $parts = $this->parseTestFile($file);

        try {
            $parser = new Parser;
            $node = $parser->parse($parts['HAML'], $file, 2);

            $renderer = new Printer;
            $node->accept($renderer);
        } catch (\Exception $e) {
            return $this->assertException($parts, $e);
        }
        $this->assertException($parts);

        file_put_contents($file . '.out', $renderer->getOutput());

        $this->assertSame($parts['EXPECT'], $renderer->getOutput());

        unlink($file . '.out');
    }

    public function getParserFixtures()
    {
        if (false !== $tests = getenv('PARSER_TESTS')) {
            $files = explode(' ', $tests);
        } else {
            $files = glob(__DIR__ . '/fixtures/parser/*.test');
        }

        return array_map(function ($file) {
            return array($file);
        }, $files);
    }
}
