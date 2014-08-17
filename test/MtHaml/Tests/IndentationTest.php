<?php

namespace MtHaml\Tests;

use MtHaml\Indentation\Undefined;

class IndentationTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider getTransitionData */
    public function testTransition($expectChar, $expectWidth, $expectLevel, $expectString, array $transitions)
    {
        $i = new Undefined();
        foreach ($transitions as $transition) {
            $i = $i->newLevel($transition);
        }
        $this->assertSame($expectChar, $i->getChar());
        $this->assertSame($expectWidth, $i->getWidth());
        $this->assertSame($expectLevel, $i->getLevel());
        $this->assertSame($expectString, $i->getString());
    }

    public function getTransitionData()
    {
        return array(
            'none' => array(
                'char' => null,
                'width' => null,
                'level' => 0,
                'string' => '',
                array(''),
            ),
            'one' => array(
                'char' => ' ',
                'width' => 2,
                'level' => 1,
                'string' => '  ',
                array('  '),
            ),
            'two' => array(
                'char' => ' ',
                'width' => 2,
                'level' => 2,
                'string' => '    ',
                array('  ', '    '),
            ),
            'two, 3 spaces' => array(
                'char' => ' ',
                'width' => 3,
                'level' => 2,
                'string' => '      ',
                array('   ', '      '),
            ),
            'two, 4 spaces' => array(
                'char' => ' ',
                'width' => 4,
                'level' => 2,
                'string' => '        ',
                array('    ', '        '),
            ),
            'two, tabs' => array(
                'char' => "\t",
                'width' => 1,
                'level' => 2,
                'string' => "\t\t",
                array("\t", "\t\t"),
            ),
            'same level' => array(
                'char' => ' ',
                'width' => 2,
                'level' => 2,
                'string' => '    ',
                array('  ', '    ', '    '),
            ),
            'undent' => array(
                'char' => ' ',
                'width' => 2,
                'level' => 1,
                'string' => '  ',
                array('  ', '    ', '  '),
            ),
            'undent many' => array(
                'char' => ' ',
                'width' => 2,
                'level' => 1,
                'string' => '  ',
                array('  ', '    ', '      ', '  '),
            ),
            'undent to zero' => array(
                'char' => ' ',
                'width' => 2,
                'level' => 0,
                'string' => '',
                array('  ', '    ', '      ', ''),
            ),
        );
    }

    /**
     * @expectedException MtHaml\Indentation\IndentationException
     * @expectedExceptionMessage Indentation can use only tabs or spaces
     */
    public function testOnlySpacesAndTabsAreAllowed()
    {
        $i = new Undefined();
        $i->newLevel("_");
    }

    /**
     * @expectedException MtHaml\Indentation\IndentationException
     * @expectedExceptionMessage Indentation can't use both tabs and spaces
     */
    public function testCanNotMixTabsAndSpaces()
    {
        $i = new Undefined();
        $i->newLevel(" \t");
    }

    /**
     * @expectedException MtHaml\Indentation\IndentationException
     * @expectedExceptionMessage The line was indented more than one level deeper than the previous line
     */
    public function testCanOnlyIndentOneLevelAtOnce()
    {
        $i = new Undefined();
        $i = $i->newLevel(" ");
        $i = $i->newLevel("    ");
    }

    /**
     * @expectedException MtHaml\Indentation\IndentationException
     * @expectedExceptionMessage Inconsistent indentation: 3 is not a multiple of 2
     */
    public function testWidthMustBeConsistent()
    {
        $i = new Undefined();
        $i = $i->newLevel("  ");
        $i = $i->newLevel("   ");
    }
}

