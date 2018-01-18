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

        $source = new \Twig_Source('<h1>{{ title }}</h1>', 'template.haml', '/somewhere/template.haml');

        $loader = $this->getMockBuilder('Twig_LoaderInterface')
            ->setMethods(['getSourceContext'])
            ->getMock();
        $loader->expects($this->once())
            ->method('getSourceContext')
            ->with('template.haml')
            ->will($this->returnValue($source));

        $hamlLoader = new Loader($env, $loader);
        $hamlLoader->getSourceContext('template.twig');
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

        $source = new \Twig_Source('%h1= title', 'template.haml', '/somewhere/template.haml');

        $loader = $this->getMockBuilder('Twig_LoaderInterface')
            ->setMethods(['getSourceContext'])
            ->getMock();
        $loader->expects($this->once())
            ->method('getSourceContext')
            ->with('template.haml')
            ->will($this->returnValue($source));


        $hamlLoader = new Loader($env, $loader);
        $hamlLoader->getSourceContext('template.haml');
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

        $source = new \Twig_Source('{% haml %} %h1= title', 'template.haml', '/somewhere/template.haml');

        $loader = $this->getMockBuilder('Twig_LoaderInterface')
            ->setMethods(['getSourceContext'])
            ->getMock();
        $loader->expects($this->once())
            ->method('getSourceContext')
            ->with('template.haml')
            ->will($this->returnValue($source));

        $hamlLoader = new Loader($env, $loader);
        $hamlLoader->getSourceContext('template.twig');
    }
}
