<?php

use PHPUnit\Framework\TestCase;

use CoreKit\Resolver;
use TemplateKit\SmartTemplate\Parser;
use TemplateKit\SmartTemplate\Compiler;

class TemplateKit_SmartTemplate_ParserTest extends TestCase
{
    protected $resolver;

    public function __construct()
    {
        parent::__construct();
        $this->resolver = new Resolver();
        $this->resolver->addPath(dirname(__DIR__).'/data');
    }

    public function testText()
    {
        Compiler::setParserOptions([ 'resolver' => $this->resolver ]);
        $parser = Compiler::getParser();

        $document = $parser->parse("Hello world");

        $this->assertInstanceOf(TemplateKit\SmartTemplate\Node\Node::class, $document);
        $this->assertInstanceOf(TemplateKit\SmartTemplate\Node\Content::class, $document->children[0]);
        $this->assertEquals("Hello world", $document->children[0]->getPhpCode());
        $this->assertEquals("Hello world", $document->getPhpCode());
    }

    public function testExpressionArgument()
    {
        Compiler::setParserOptions([ 'resolver' => $this->resolver ]);
        $parser = Compiler::getParser();

        $document = $parser->parse("{translate 'The message'}");

        $this->assertEquals("<?php echo _('The message'); ?>", $document->getPhpCode());
    }

    public function testIfElseIfElse()
    {
        Compiler::setParserOptions([ 'resolver' => $this->resolver ]);
        $parser = Compiler::getParser();

        $document = $parser->parse("{if \$first}First{elseif \$second}Second{else}Third{/if}");

        $this->assertInstanceOf(TemplateKit\SmartTemplate\Node\Node::class, $document);
        $this->assertEquals(
            "<?php if (\$first): ?>First<?php elseif (\$second): ?>Second<?php else: ?>Third<?php endif; ?>",
            $document->getPhpCode()
        );

        $document = $parser->parse("{if \$firstorsecond and \$second}First and second{else}Third{/if}");

        $this->assertInstanceOf(TemplateKit\SmartTemplate\Node\Node::class, $document);
        $this->assertEquals(
            "<?php if (\$firstorsecond && \$second): ?>First and second<?php else: ?>Third<?php endif; ?>",
            $document->getPhpCode()
        );
    }

    public function testForeach()
    {
        Compiler::setParserOptions([ 'resolver' => $this->resolver ]);
        $parser = Compiler::getParser();

        $document = $parser->parse("{for \$item in \$collection}{\$item->name}{/for}");

        $this->assertInstanceOf(TemplateKit\SmartTemplate\Node\Node::class, $document);
        $this->assertEquals(
            "<?php foreach (\$collection as \$item): ?><?php echo \$item->name; ?><?php endforeach; ?>",
            $document->getPhpCode()
        );

        $document = $parser->parse("{for \$index,\$item in \$collection}{\$index}: {\$item->name}{/for}");

        $this->assertInstanceOf(TemplateKit\SmartTemplate\Node\Node::class, $document);
        $this->assertEquals(
            "<?php foreach (\$collection as \$index => \$item): ?><?php echo \$index; ?>: <?php echo \$item->name; ?><?php endforeach; ?>",
            $document->getPhpCode()
        );
    }
}
