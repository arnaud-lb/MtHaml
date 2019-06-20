<?php
namespace MtHaml\Tests\Support\Twig;

use MtHaml\Support\Twig\Loader;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    protected $getSourceMethod;

    public function testLoadSimpleTwigTemplate()
    {
        $env = $this->getMockBuilder('MtHaml\Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $env->expects($this->never())
            ->method('compileString');

        $loader = $this->createMock('Twig_LoaderInterface');

        $loader->expects($this->once())
            ->method($this->getSourceMethod)
            ->will($this->returnValue($this->getSource('<h1>{{ title }}</h1>', 'template.twig')));

        $hamlLoader = new Loader($env, $loader);
        $hamlLoader->{$this->getSourceMethod}('template.twig');
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

        $loader = $this->createMock('Twig_LoaderInterface');

        $loader->expects($this->once())
            ->method($this->getSourceMethod)
            ->will($this->returnValue($this->getSource('%h1= title', 'template.haml')));

        $hamlLoader = new Loader($env, $loader);
        $hamlLoader->{$this->getSourceMethod}('template.haml');
    }

    public function testLoadTwigWithHamlTemplate()
    {
        $env = $this->getMockBuilder('MtHaml\Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $env->expects($this->once())
            ->method('compileString')
            ->with('           %h1= title')
            ->will($this->returnValue('<h1>{{ title }}</h1>', 'template.twig'));

        $loader = $this->createMock('Twig_LoaderInterface');

        $loader->expects($this->once())
            ->method($this->getSourceMethod)
            ->will($this->returnValue($this->getSource('{% haml %} %h1= title', 'template.twig')));

        $hamlLoader = new Loader($env, $loader);
        $hamlLoader->{$this->getSourceMethod}('template.twig');
    }

    protected function getSource($source, $name)
    {
        if ($this->getSourceMethod === 'getSourceContext') {
            return new \Twig\Source($source, $name);
        }

        return $source;
    }

    protected function setUp()
    {
        $loader = $this->createMock('Twig_LoaderInterface');
        $this->getSourceMethod = method_exists($loader, 'getSourceContext') ? 'getSourceContext' : 'getSource';
    }
}
