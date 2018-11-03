<?php

namespace TemplateKit\SmartTemplate;

class Compiler
{
    protected static $parserOptions = [];
    protected static $cacheOptions = [];
    protected static $workingDir = __DIR__;

    public static function setParserOptions(array $options)
    {
        foreach ($options as $key => $value) {
            self::$parserOptions[$key] = $value;
        }
    }

    public static function setCacheOptions(array $options)
    {
        foreach ($options as $key => $value) {
            self::$cacheOptions[$key] = $value;
        }
    }

    public static function getParser()
    {
        return new Parser(self::$parserOptions);
    }

    public static function getCompiledFile($filename)
    {
        if (!isset(self::$cacheOptions['cacheDir'])) {
            throw new \Exception("Cache dir not set");
        }

        $cachename = rtrim(self::$cacheOptions['cacheDir']) . '/' .
            md5($filename) . '_' . basename($filename, '.tpl') . '.php';

        if (!file_exists($cachename) || filemtime($cachename) < filemtime($filename)) {
            // Parse template
            $wd = self::getWorkingDir();
            self::setWorkingDir(dirname($filename));
            $parser = self::getParser();
            $document = $parser->parse(file_get_contents($filename));
            $phpCode = $document->getPhpRootCode();
            self::setWorkingDir($wd);

            // Add all includes
            $inc = '';
            foreach ($document->getIncludes() as $include) {
                $inc .= 'require_once "' . $include . '";' . PHP_EOL;
            }
            if ($inc) {
                $phpCode = '<?php' . PHP_EOL . $inc . PHP_EOL . '?>' . PHP_EOL . $phpCode;
            }

            // Store cache
            file_put_contents($cachename, $phpCode);
        }

        return $cachename;
    }

    public static function setWorkingDir($dir)
    {
        self::$workingDir = rtrim($dir, '/') . '/';
    }

    public static function getWorkingDir()
    {
        return self::$workingDir;
    }
}
