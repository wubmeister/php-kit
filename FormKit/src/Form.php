<?php

namespace FormKit;

class Form
{
    protected $name;
    protected $fields = [];
    protected $fieldsByName = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function addField(Field $field)
    {
        $field->setForm($this);
        $this->fields[] = $field;
        $this->fieldsByName[$field->getName()] = $field;
    }
}
