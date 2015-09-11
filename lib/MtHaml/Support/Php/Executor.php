<?php

namespace MtHaml\Support\Php;

use MtHaml\Environment;
use MtHaml\Exception;

/**
 * Executor is a simple helper that can compile haml files, cache them, and execute them
 */
class Executor
{
    private $environment;

    private $options = array(
        // Cache directory to store compiled templates
        'cache' => null,
        // Whether to by-pass cache, useful when debugging
        'debug' => false,
    );

    public function __construct(Environment $environment, array $options)
    {
        $this->environment = $environment;
        $this->options = $options + $this->options;

        if (!$this->options['cache']) {
            throw new Exception("A 'cache' option must be defined");
        }
    }

    /**
     * Executes and displays the template $file, with variables $variables
     */
    public function display($file, array $variables)
    {
        $fun = $this->compileFile($file);
        $fun($variables);
    }

    /**
     * Executes the template $file with variables $variables, and returns its output
     */
    public function render($file, array $variables)
    {
        $level = ob_get_level();
        ob_start();

        try {
            $this->display($file, $variables);
        } catch (\Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }

        return ob_get_clean();
    }

    public function warmup($file)
    {
        $this->compileFile($file);
    }

    private function compileFile($file)
    {
        if (!file_exists($file)) {
            throw new Exception(sprintf(
                "File does not exist: `%s`"
                , $file
            ));
        }

        $hash = hash('sha256', $file);
        $funName = '__MtHamlTemplate_' . $hash;

        if (function_exists($funName)) {
            return $funName;
        }

        $cacheFile = $this->options['cache']
            . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/'
            . substr($hash, 4) . '_' . basename($file) . '.php';

        $fileMtime = filemtime($file);

        if ($this->options['debug'] || !file_exists($cacheFile) || filemtime($cacheFile) !== $fileMtime) {

            $hamlCode = file_get_contents($file);

            if (false === $hamlCode) {
                throw new Exception(sprintf(
                    "Failed reading file: `%s`"
                    , $file
                ));
            }

            $compiledCode = $this->environment->compileString($hamlCode, $file);
            $compiledCode = $this->wrapCompiledCode($compiledCode, $funName);

            $this->writeCacheFile($cacheFile, $compiledCode, $fileMtime);
        }

        require_once $cacheFile;

        return $funName;
    }

    private function wrapCompiledCode($code, $funName)
    {
        // The code is wrapped in a function so that it can be parsed
        // once, and executed multiple times. This is faster than repeatedly
        // including the same PHP file.
        return <<<PHP
<?php

function $funName(\$__variables)
{
    extract(\$__variables);
?>$code<?php
}
PHP;
    }

    private function writeCacheFile($cacheFile, $contents, $timestamp)
    {

        $dir = dirname($cacheFile);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new Exception(sprintf(
                    "Failed creating cache directory: `%s`"
                    , $dir
                ));
            }
        }

        if (!is_writeable($dir)) {
            throw new Exception(sprintf(
                "Cache directory is not writeable: `%s`"
                , $dir
            ));
        }

        $tmpFile = tempnam($dir, basename($cacheFile));

        if (false === file_put_contents($tmpFile, $contents)) {
            throw new Exception(sprintf(
                "Failed writing cache file: `%s`"
                , $tmpFile
            ));
        }

        if (!rename($tmpFile, $cacheFile)) {
            @unlink($tmpFile);
            throw new Exception(sprintf(
                "Failed writing cache file: `%s`"
                , $cacheFile
            ));
        }

        if (!touch($cacheFile, $timestamp)) {
            throw new Exception(sprintf(
                "Failed writing cache file: `%s`"
                , $cacheFile
            ));
        }

    }
}
