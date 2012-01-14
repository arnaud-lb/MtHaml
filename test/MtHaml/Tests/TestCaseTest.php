<?php

namespace MtHaml\Tests;

require_once __DIR__ . '/TestCase.php';

class TestCaseTest extends TestCase
{
    public function testAssertExceptionReturnsIfNothingExceptedAndThrown()
    {
        $this->assertException(array(), null);
    }

    /**
     * @expectedException LogicException
     */
    public function testAssertExceptionThrowsIfNothingExpectedButThrown()
    {
        $e = new \LogicException;
        $this->assertException(array(), $e);
    }

    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting that exception of type "Foo"
     */
    public function testAssertExceptionFailsIfExpectedButNothingThrown()
    {
        $this->assertException(array('EXCEPTION' => "Foo\n"), null);
    }

    /**
     * @expectedException LogicException
     */
    public function testAssertExceptionThrowsIfWrongExceptionClass()
    {
        $e = new \LogicException;
        $this->assertException(array('EXCEPTION' => "Foo\n"), $e);
    }

    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting that 'foo' matches PCRE pattern "~^(bar)$~".
     */
    public function testAssertExceptionFailsIfMessageDoesNotMatch()
    {
        $e = new \Exception('foo');
        $this->assertException(array('EXCEPTION' => "Exception\nbar"), $e);
    }
}
