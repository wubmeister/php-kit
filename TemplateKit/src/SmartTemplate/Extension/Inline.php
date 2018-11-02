<?php

namespace TemplateKit\SmartTemplate\Extension;

class Inline
{
    protected static $instances = [];

    public static function instance()
    {
        $cls = get_called_class();
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new $cls();
        }
        return self::$instances[$cls];
    }
}