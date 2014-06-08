<?php
namespace MtHaml\Tests\Support\Twig;

use MtHaml\Support\Twig\Loader;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadSimpleTwigTemplate()
    {
        $env = $this->getMockBuilder('MtHaml\Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $env->expects($this->never())
            ->method('compileString');

        $loader = $this->getMock('Twig_LoaderInterface');
        $loader->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue('<h1>{{ title }}</h1>'));

        $hamlLoader = new Loader($env, $loader);
        $hamlLoader->getSource('template.twig');
    }

    public function testLoadHamlTemplate()
    {
        $env = $this->getMockBuilder('MtHaml\Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $env->expects($this->once())
            ->method('compileString')
            ->with('%h1= title')
            ->will($this->returnValue('<h1>{{ title }}</h1>'));

        $loader = $this->getMock('Twig_LoaderInterface');
        $loader->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue('%h1= title'));

        $hamlLoader = new Loader($env, $loader);
        $hamlLoader->getSource('template.haml');
    }

    public function testLoadTwigWithHamlTemplate()
    {
        $env = $this->getMockBuilder('MtHaml\Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $env->expects($this->once())
            ->method('compileString')
            ->with('           %h1= title')
            ->will($this->returnValue('<h1>{{ title }}</h1>'));

        $loader = $this->getMock('Twig_LoaderInterface');
        $loader->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue('{% haml %} %h1= title'));

        $hamlLoader = new Loader($env, $loader);
        $hamlLoader->getSource('template.twig');
    }
}
