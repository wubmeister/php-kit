<?php

namespace TemplateKit\SmartTemplate\Node;

class Content extends Node
{
    public $content;

    public function __construct(string $content)
    {
        // Strip PHP code
        $offset = 0;
        while ($offset < strlen($content)) {
            $pos = strpos($content, '<?', $offset);
            if ($pos === false) break;
            if (strtolower(substr($content, $pos+2, 3)) == 'xml') {
                $pos2 = strpos($content, '?>', $offset);
                $offset = $pos2 === false ? $len : $pos2 + 2;
            } else {
                $pos2 = strpos($content, '?>', $offset);
                $content = substr($content, 0, $pos) . ($pos2 === false ? '' : substr($content, $pos2 + 2));
            }
        }

        // Prevent <?xml ... ? > from being interpreted as PHP tags
        $content = strtr($content, [ '<?' => '<?php echo "<?";?>', '?>' => '<?php echo "?>";?>' ]);

        $this->content = $content;
    }

    public function getPhpCode()
    {
        return $this->content;
    }
}