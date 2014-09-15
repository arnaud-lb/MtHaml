<?php

namespace MtHaml\Tests\Support\Php;

use MtHaml\Support\Php\Executor;

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    private $cacheDir;

    public function setUp()
    {
        $this->cacheDir = $this->tempdir(sys_get_temp_dir());
    }

    public function tearDown()
    {
        if (null !== $this->cacheDir) {
            $this->unlinkr($this->cacheDir);
        }
    }

    public function testRender()
    {
        $tpl = __DIR__ . '/../../fixtures/Support/Php/Executor.001.haml';

        $env = $this->getMockBuilder('MtHaml\Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $env->expects($this->any())
            ->method('compileString')
            ->with("%p= 6 * \$var\n", $tpl)
            ->will($this->returnValue('<p><?php echo 6 * $var; ?></p>'));

        $executor = new Executor($env, array(
            'cache' => $this->cacheDir,
        ));

        $vars = array(
            'var' => 7,
        );
        $result = $executor->render($tpl, $vars);

        $this->assertSame('<p>42</p>', $result);
    }

    private function tempdir($root)
    {
        if (!is_dir($root) || !is_writeable($root)) {
            throw new \Exception(sprintf(
                "Cannot create directory at `%s`"
                , $root
            ));
        }

        for ($i = 0; $i < 100; $i++) {
            $name = bin2hex(pack('d', rand()));
            if (mkdir($root . '/' . $name)) {
                return $root . '/' . $name;
            }
        }

        throw new \Exception(sprintf(
            "Failed creating temporary directory at `%s`"
            , $root
        ));
    }

    private function unlinkr($dir)
    {
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $fileinfo) {
            if ($fileinfo->isDir()) {
                rmdir($fileinfo->getPathname());
            } else {
                unlink($fileinfo->getPathname());
            }
        }
        rmdir($dir);
    }
}
