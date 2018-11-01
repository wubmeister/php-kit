<?php

namespace FormKit;

class Form
{
    protected static $data = [];
    protected static $postData = [];
    protected static $errors = [];
    protected static $formNames = [];
    protected static $currFormName = null;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public static function addData($data)
    {
        self::$data = array_merge(self::$data, $data);
    }

    public static function addPostData($data)
    {
        self::$postData = array_merge(self::$postData, $data);
    }

    protected static function getValue($name, $defaultValue = '')
    {
        return isset(self::$postData[$name])
            ? self::$postData[$name]
            : (isset(self::$data[$name]) ? self::$data[$name] : $defaultValue);
    }

    public static function addError($name, $error)
    {
        if (!isset(self::$errors[$name])) {
            self::$errors[$name] = [];
        }
        self::$errors[$name][] = $error;
    }

    protected static function buildAttributes($attributes)
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . ($value !== true ? '="' . htmlspecialchars($value) . '"' : '');
        }
        return $html;
    }

    protected static function buildId($name)
    {
        $id = trim(preg_replace("/[^a-zA-Z0-9_]+/", "-", $name), "-");
        if (self::$currFormName) {
            $id = self::$currFormName . "-" . $id;
        }
        return $id;
    }

    public static function open(array $options = [])
    {
        self::$formNames[] = self::$currFormName = $options['name']??[];
        $tag = '<form method="' . ($options['method']??'post') . '" action="' . ($options['action']??'') . '" enctype="' . ($options['enctype']??'application/x-www-form-urlencoded') . '"';
        unset($options['method'], $options['action'], $options['enctype']);
        $tag .= self::buildAttributes($options) . '>';
        return $tag;
    }

    public static function close()
    {
        return '</form>';
    }

    public static function errors($name)
    {
        if (!isset(self::$errors[$name])) return '';

        $html = '';
        foreach (self::$errors[$name] as $key => $error) {
            $html .= $error . '<br/>';
        }

        return $html;
    }

    public static function hasErrors($name)
    {
        return isset(self::$errors[$name]) && count(self::$errors[$name]) > 0;
    }

    public static function label($name, $label, $attributes = [])
    {
        return '<label for="' . self::buildId($name) . '"' . self::buildAttributes($attributes) . '>' . $label . '</label>';
    }

    public static function checkbox($name, $initValue = '', $attributes = [])
    {
        $value = self::getValue($name, $initValue);
        $html = '<input type="hidden" name="' . $name . '" value="0" />';
        $html .= '<input type="checkbox" name="' . $name . '" id="' . self::buildId($name) . '" value="1"' . ($value == 1 ? ' checked' : '') . self::buildAttributes($attributes) . ' />';
        return $html;
    }

    public static function textField($name, $initValue = '', $attributes = [])
    {
        if (self::hasErrors($name)) {
            $attributes['class'] = isset($attributes['class']) ? $attributes['class'] . ' is-invalid' : 'is-invalid';
        }
        $value = self::getValue($name, $initValue);
        return '<input type="text" name="' . $name . '" id="' . self::buildId($name) . '" value="' . $value . '"' . self::buildAttributes($attributes) . ' />';
    }

    public static function textarea($name, $initValue = '', $attributes = [])
    {
        $value = self::getValue($name, $initValue);
        return '<textarea name="' . $name . '" id="' . self::buildId($name) . '"' . self::buildAttributes($attributes) . '>' . htmlspecialchars($value) . '</textarea>';
    }

    public static function emailField($name, $initValue = '', $attributes = [])
    {
        $value = self::getValue($name, $initValue);
        return '<input type="email" name="' . $name . '" id="' . self::buildId($name) . '" value="' . $value . '"' . self::buildAttributes($attributes) . ' />';
    }

    public static function passwordField($name, $initValue = '', $attributes = [])
    {
        $value = self::getValue($name, $initValue);
        return '<input type="password" name="' . $name . '" id="' . self::buildId($name) . '" value="' . $value . '"' . self::buildAttributes($attributes) . ' />';
    }

    public static function select($name, $options, $initValue = '', $attributes = [])
    {
        // TODO: optgroups
        $selectedValue = self::getValue($name, $initValue);
        $html = '<select name="' . $name . '" id="' . self::buildId($name) . '"' . self::buildAttributes($attributes) . '>';
        foreach ($options as $value => $label) {
            $html .= '<option value="' . $value . '"' . ($value == $selectedValue ? ' selected' : '') . '>' . $label . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public static function submit($caption, $attributes = [])
    {
        return '<button type="submit"' . self::buildAttributes($attributes) . '>' . $caption . '</button>';
    }
}
