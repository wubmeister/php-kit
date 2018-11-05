<?php

use PHPUnit\Framework\TestCase;

use CoreKit\Resolver;

class TemplateKit_ResolverTest extends TestCase
{
    public function testResolve()
    {
        $resolver = new Resolver();
        $resolver->addPath(__DIR__ . '/data');

        $file = $resolver->resolve('file.{phtml,tpl}');
        $this->assertEquals(__DIR__ . '/data/file.phtml', $file);
    }
}
