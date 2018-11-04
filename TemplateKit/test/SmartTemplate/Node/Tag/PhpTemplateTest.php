<?php

use PHPUnit\Framework\TestCase;

use TemplateKit\SmartTemplate\Node\Tag\PhpTemplate;

class TemplateKit_SmartTemplate_Node_Tag_PhpTemplateTest extends TestCase
{
    public function testCreate()
    {
        $tag = new PhpTemplate('mytag', dirname(dirname(dirname(__DIR__))).'/data/simple.phtml', []);
        $this->assertEquals("<?php \$attr = []; ?>\nSimple tag\n", $tag->getPhpCode());
    }

    public function testOptions()
    {
        $tag = new PhpTemplate('mytag', dirname(dirname(dirname(__DIR__))).'/data/options.phtml', []);
        $this->assertFalse($tag->isSelfClosing);
    }
}
