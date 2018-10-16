<?php

namespace CoreKit;

class Config
{
    protected $data = [];

    public function __construct($data = null)
    {
        if ($data) {
            foreach ($data as $key => $value) {
                if (is_array($value) && !isset($value[0])) {
                    $value = new Config($value);
                }
                $this->data[$key] = $value;
            }
        }
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    protected function mergeArray($array)
    {
        foreach ($array as $key => $value) {
            if (isset($this->data[$key]) && $this->data[$key] instanceof Config && $value instanceof Config) {
                $this->data[$key]->mergeArray($value->data);
            } else {
                $this->data[$key] = $value;
            }
        }
    }

    protected function loadFile($file)
    {
        $array = include $file;
        $this->mergeArray((new Config($array))->data);
    }

    public function loadFromFiles(array $files)
    {
        foreach ($files as $file) {
            $this->loadFile($file);
        }
    }

    public function toArray()
    {
        $array = $this->data;
        foreach ($array as $key => $value) {
            if ($value instanceof Config) {
                $array[$key] = $value->toArray();
            }
        }
        return $array;
    }
}
