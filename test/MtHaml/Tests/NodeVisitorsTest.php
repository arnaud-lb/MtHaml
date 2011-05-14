<?php

namespace MtHaml\Tests\NodeVisitor;

use MtHaml\Tests\TestCase;
use MtHaml\NodeVisitor\Autoclose;
use MtHaml\NodeVisitor\Printer;
use MtHaml\Parser;

require_once __DIR__ . '/TestCase.php';

class AutocloseTest extends TestCase
{
    /** @dataProvider getAutocloseFixtures */
    public function testAutoclose($file)
    {
        $parts = $this->parseTestFile($file);

        try {
            $parser = new Parser;
            $node = $parser->parse($parts['HAML'], $file, 2);

            eval($parts['FILE']);

            $renderer = new Printer;
            $node->accept($renderer);
        } catch(\Exception $e) {
            return $this->assertException($parts, $e);
        }
        $this->assertException($parts);

        file_put_contents($file . '.out', $renderer->getOutput());

        $this->assertSame($parts['EXPECT'], $renderer->getOutput());

        unlink($file . '.out');

    }

    public function getAutocloseFixtures()
    {
        return array_map(function($file) {
            return array($file);
        }, glob(__DIR__ . '/fixtures/nodevisitors/*.test'));
    }
}

