<?php

namespace TemplateKit;

use TemplateKit\SmartTemplate\SmartTemplate;


class Template implements TemplateInterface
{
    protected $parent;
    protected $variables = [];
    protected $file;

    protected static $captureStack = [];
    protected static $captures = [];

    public function __construct($file)
    {
        $this->file = $file;
    }

    public static function factory($file)
    {
        switch (pathinfo($file, PATHINFO_EXTENSION)) {
            case 'tpl':
                return new SmartTemplate($file);

            default:
                return new Template($file);
        }
    }

    public function assign(string $name, $value)
    {
        if ($value instanceof Template) {
            $value->parent = $this;
        }

        $this->variables[$name] = $value;
    }

    public function unassign(string $name)
    {
        if (isset($this->variables[$name])) {
            unset($this->variables[$name]);
        }
    }

    protected function getVariables()
    {
        $variables = $this->variables;
        if ($this->parent) {
            $variables = array_merge($this->parent->getVariables(), $variables);
        }

        return $variables;
    }

    public function render()
    {
        $variables = $this->getVariables();
        extract($variables);

        ob_start();
        include $this->file;
        $contents = ob_get_clean();

        return $contents;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function capture(string $name, $append = false)
    {
        self::$captureStack[] = [ 'name' => $name, 'append' => $append ];
        ob_start();
    }

    public function endCapture()
    {
        $contents = ob_get_clean();
        $capture = array_pop(self::$captureStack);
        if ($capture['append'] && isset(self::$captures[$capture['name']])) {
            self::$captures[$capture['name']] .= $contents;
        } else {
            self::$captures[$capture['name']] = $contents;
        }
    }

    public function getCapture(string $name)
    {
        if (!isset(self::$captures[$name])) {
            return '';
        }
        return self::$captures[$name];
    }
}
