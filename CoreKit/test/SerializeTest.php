<?php

use PHPUnit\Framework\TestCase;

use CoreKit\Serialize;

class TemplateKit_SerializeTest extends TestCase
{
    public function testToPhp()
    {
        $value = 'I am a "string"';
        $this->assertEquals('"I am a \\"string\\""', Serialize::toPhp($value));

        $value = [ 'I am a string', 'key' => 'value' ];
        $this->assertEquals('[0 => "I am a string", "key" => "value"]', Serialize::toPhp($value));

        $value = [ 'I am a string', 'key' => 'value' ];
        $this->assertEquals("[\n    0 => \"I am a string\",\n    \"key\" => \"value\"\n]", Serialize::toPhp($value, Serialize::OPT_MULTILINE));
    }
}
