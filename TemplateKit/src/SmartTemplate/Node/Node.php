<?php

namespace TemplateKit\SmartTemplate\Node;

class Node
{
    public $name = '';
    public $children = [];

    protected $includes = [];
    protected $parent;

    public function appendChild(Node $child)
    {
        $child->parent = $this;
        $this->children[] = $child;
    }

    public function getPhpCode()
    {
        $php = '';
        foreach ($this->children as $child) {
            $php .= $child->getPhpCode();
        }

        return $php;
    }

    public function getPhpRootCode()
    {
        $php = $this->getPhpCode();

        if (preg_match_all('/\buse ([a-zA-Z0-9_\\\\]+);/', $php, $matches, PREG_SET_ORDER)) {
            $uses = [];
            foreach ($matches as $match) {
                $uses[] = $match[1];
            }
            $php = preg_replace('/\buse ([a-zA-Z0-9_\\\\]+);/', '', $php);
            $php = "<?php" . PHP_EOL . "use " . implode(";\nuse ", array_unique($uses)) . ";\n?>" . PHP_EOL . $php;
        }

        $php = preg_replace('/<\?php\s*\?>/', '', $php);

        return $php;
    }

    public function getIncludes()
    {
        $includes = $this->includes;
        foreach ($this->children as $child) {
            $includes = array_merge($includes, $child->getIncludes());
        }

        return $includes;
    }

    public function dehydrate()
    {
        $dry = [
            "tag" => $this->name
        ];
        if (count($this->children)) {
            $dry['children'] = [];
            foreach ($this->children as $child) {
                $dry['children'][] = $child->dehydrate();
            }
        }
        return $dry;
    }
}
