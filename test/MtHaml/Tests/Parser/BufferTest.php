<?php

namespace MtHaml\Tests\Parser;

use MtHaml\Parser\Buffer;

class BufferTest extends \PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $buffer = new Buffer("  abc\n    def\nghi");

        $this->assertTrue($buffer->nextLine());
        $this->assertSame("  abc", $buffer->getLine());

        $this->assertTrue($buffer->match('~z*~A', $match));
        $this->assertSame(array(
            '',
            'pos' => array(
                array('lineno' => 1, 'column' => 0),
            ),
        ), $match);
        $this->assertSame("  abc", $buffer->getLine());

        $this->assertTrue($buffer->match('~(\s*)(a)~A', $match));
        $this->assertSame(array(
            '  a',
            '  ',
            'a',
            'pos' => array(
                array('lineno' => 1, 'column' => 0),
                array('lineno' => 1, 'column' => 0),
                array('lineno' => 1, 'column' => 2),
            ),
        ), $match);
        $this->assertSame("bc", $buffer->getLine());

        $this->assertTrue($buffer->nextLine());
        $this->assertSame("    def", $buffer->getLine());
        $this->assertSame(2, $buffer->getLineno());

        $this->assertSame(' ', $buffer->peekChar());
        $this->assertSame("    def", $buffer->getLine());
        $this->assertSame(1, $buffer->getColumn());

        $this->assertSame(' ', $buffer->eatChar());
        $this->assertSame("   def", $buffer->getLine());
        $this->assertSame(2, $buffer->getColumn());

        $buffer->skipWs();
        $this->assertSame('def', $buffer->getLine());
        $this->assertSame(5, $buffer->getColumn());

        $this->assertTrue($buffer->nextLine());
        $this->assertSame('ghi', $buffer->getLine());
        $this->assertSame(3, $buffer->getLineno());

        $this->assertFalse($buffer->nextLine());
    }

    public function testEatChars()
    {
        $buffer = new Buffer("abcdef");

        $buffer->nextLine();

        $chars = $buffer->eatChars(2);
        $this->assertSame("ab", $chars);
        $this->assertSame("cdef", $buffer->getLine());
        $this->assertSame(3, $buffer->getColumn());

        $chars = $buffer->eatChars(5);
        $this->assertSame("cdef", $chars);
        $this->assertSame("", $buffer->getLine());
        $this->assertSame(7, $buffer->getColumn());
    }
}
