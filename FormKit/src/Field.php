<?php

namespace FormKit;

class Field
{
    protected $form;
    protected $name;
    protected $value;
    protected $isArrayFlag = false;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFullName()
    {
        $name = $this->getName();
        if ($this->isArray()) {
            $name .= '[]';
        }
        return $name;
    }

    public function getId()
    {
        $name = $this->getFullName();
        $id = trim(preg_replace("/[^a-z0-9]+/", "-", $name), "-");
        return $id;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setForm(Form $form)
    {
        $this->form = $form;
    }

    public function isArray()
    {
        return $this->isArrayFlag;
    }
}
