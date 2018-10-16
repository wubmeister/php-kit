<?php

namespace FormKit;

class Form
{
    protected static $postData = [];
    protected static $formNames = [];
    protected static $currFormName = null;

    public function __construct($name)
    {
        $this->name = $name;
    }

    protected static function buildAttributes($attributes)
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= ' '.$key.'="'.htmlspecialchars($value).'"';
        }
        return $html;
    }

    protected static function buildId($name)
    {
        $id = trim(preg_replace("/[^a-zA-Z0-9]+/", "-", $name), "-");
        if (self::$currFormName) {
            $id = self::$currFormName . "-" . $id;
        }
        return $id;
    }

    public static function open(array $options)
    {
        self::$formNames[] = self::$currFormName = $options['name']??[];
        $tag = '<form method="' . ($options['method']??'post') . '" action="' . $options['action'] . '" enctype="' . ($options['enctype']??'application/x-www-form-urlencoded') . '"';
        unset($options['method'], $options['action'], $options['enctype']);
        $tag .= self::buildAttributes($options) . '>';
        return $tag;
    }

    public static function close()
    {
        return '</form>';
    }

    public static function label($name, $label, $attributes = [])
    {
        return '<label for="' . self::buildId($name) . '"' . self::buildAttributes($attributes) . '>' . $label . '</label>';
    }

    public static function textField($name, $initValue = '', $attributes = [])
    {
        $value = isset(self::$postData[$name]) ? self::$postData[$name] : $initValue;
        return '<input type="text" name="' . $name . '" id="' . self::buildId($name) . '" value="' . $value . '"' . self::buildAttributes($attributes) . ' />';
    }

    public static function emailField($name, $initValue = '', $attributes = [])
    {
        $value = isset(self::$postData[$name]) ? self::$postData[$name] : $initValue;
        return '<input type="email" name="' . $name . '" id="' . self::buildId($name) . '" value="' . $value . '"' . self::buildAttributes($attributes) . ' />';
    }

    public static function passwordField($name, $initValue = '', $attributes = [])
    {
        $value = isset(self::$postData[$name]) ? self::$postData[$name] : $initValue;
        return '<input type="password" name="' . $name . '" id="' . self::buildId($name) . '" value="' . $value . '"' . self::buildAttributes($attributes) . ' />';
    }

    public static function submit($caption, $attributes = [])
    {
        return '<button type="submit"' . self::buildAttributes($attributes) . '>' . $caption . '</button>';
    }
}
