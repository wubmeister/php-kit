<?php

namespace CoreKit;

class Navigation
{
    const DATA_IS_ROOT_CHILDREN = 1;
    const DATA_IS_NESTED_SET = 2;

    protected $data;
    protected $identifier;
    protected $children = [];

    public $isActive;

    public function __construct($data = null, $dataFlags = 0)
    {
        if ($dataFlags & self::DATA_IS_ROOT_CHILDREN) {
            $this->data = [];
            foreach ($data as $identifier => $childData) {
                $child = new self($childData);
                if (!$child->identifier && !is_numeric($identifier)) {
                    $child->identifier = $identifier;
                }
                $this->children[] = $child;
            }
        } else {
            if (isset($data['children'])) {
                foreach ($data['children'] as $identifier => $childData) {
                    $child = new self($childData);
                    if (!$child->identifier && !is_numeric($identifier)) {
                        $child->identifier = $identifier;
                    }
                    $this->children[] = $child;
                }
                unset($data['children']);
            }

            if (isset($data['identifier'])) {
                $this->identifier = $data['identifier'];
            }

            $this->data = $data;
        }
    }

    public function __get($name)
    {
        if ($name == 'children') return $this->children;
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    public function setActivePath($path)
    {
        if (!is_array($path)) {
            $path = explode('/', $path);
        }
        if (!$this->identifier) {
            if (count($path) > 0) {
                foreach ($this->children as $child) {
                    if ($child->setActivePath($path)) return true;
                }
            }
        } else {
            $identifier = array_shift($path);
            if ($this->identifier == $identifier) {
                $this->isActive = true;
                if (count($path) > 0) {
                    foreach ($this->children as $child) {
                        if ($child->setActivePath($path)) break;
                    }
                }
                return true;
            }
        }

        return false;
    }

    public function getActiveLevel($level)
    {
        if ($level == 0) {
            return $this->isActive ? $this : null;
        }

        foreach ($this->children as $child) {
            if ($node = $child->getActiveLevel($level-1)) {
                return $node;
            }
        }

        return null;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function hasChildren()
    {
        return count($this->children) > 0;
    }
}
