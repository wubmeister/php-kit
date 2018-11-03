<?php

namespace TemplateKit\SmartTemplate;

class Compiler
{
    protected static $parserOptions = [];
    protected static $cacheOptions = [];

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
            $parser = self::getParser();
            $document = $parser->parse(file_get_contents($filename));
            $phpCode = $document->getPhpCode();

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
}
