<?php

namespace CoreKit;

class Serialize
{
    const OPT_MULTILINE = 1;

    function toPhp($var, $options = 0, $indent = 0)
    {
        if (is_null($var)) return 'null';
        if (is_bool($var)) return $var ? 'true' : 'false';
        if (is_int($var) || is_float($var)) return (string)$var;
        if (is_array($var))  {
            if ($options & self::OPT_MULTILINE) {
                $php = '[' . PHP_EOL;
            } else {
                $php = '[';
            }

            $index = 0;
            foreach ($var as $key => $value) {
                if ($index > 0) $php .= ',';
                $php .= ($options & self::OPT_MULTILINE) ? ($index > 0 ? PHP_EOL : '') . str_repeat('    ', $indent+1) : ($index > 0 ? ' ' : '');
                $php .= is_numeric($key) ? (string)$key : '"'.str_replace('"', '\\"', $key).'"';
                $php .= ' => ' . self::toPhp($value, $options, $indent + 1);
                $index++;
            }
            if ($options & self::OPT_MULTILINE) {
                $php .= PHP_EOL . str_repeat('    ', $indent);
            }
            $php .= ']';

            return $php;
        }
        $var = (string)$var;
        return '"' . str_replace('"', '\\"', $var) . '"';
    }
}
