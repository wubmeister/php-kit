<?php

use PHPUnit\Framework\TestCase;

use TemplateKit\Template;
use TemplateKit\SmartTemplate\SmartTemplate;

class TemplateKit_TemplateTest extends TestCase
{
    public function testFactory()
    {
        $template = Template::factory("template.phtml");
        $this->assertInstanceOf(Template::class, $template);
        $template = Template::factory("template.tpl");
        $this->assertInstanceOf(SmartTemplate::class, $template);
    }
}
