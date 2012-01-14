<?php

namespace MtHaml\Tests;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public function assertException($parts, \Exception $e = null)
    {
        if (empty($parts['EXCEPTION'])) {
            if (null !== $e) {
                throw $e;
            }
            return;
        }

        list($class, $message) = explode("\n", $parts['EXCEPTION'], 2);

        if (!empty($class)) {
            if (null === $e) {
                $this->assertThat(
                    NULL,
                    new \PHPUnit_Framework_Constraint_Exception($class)
                );
            }
            if (get_class($e) !== $class) {
                throw $e;
            }
        }
        if (!empty($message)) {
            $re = addcslashes($message, '~');
            $re = "~^($re)$~";
            $this->assertRegexp($re, $e->getMessage());
        }
    }

    public function parseTestFile($file)
    {
        $contents = file_get_contents($file);
        $splits = preg_split('#^--(.*)--$#m', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);
        $parts = array();

        while (false !== $key = next($splits)) {
            $parts[$key] = substr(next($splits), 1, -1);
        }

        return $parts;
    }
}

