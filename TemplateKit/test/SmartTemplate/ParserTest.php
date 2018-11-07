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

    public function testNamespace()
    {
        Compiler::setParserOptions([ 'resolver' => $this->resolver ]);
        $parser = Compiler::getParser();

        $document = $parser->parse("{namespace:tagname}");

        $this->assertInstanceOf(TemplateKit\SmartTemplate\Node\Node::class, $document);
        $this->assertInstanceOf(TemplateKit\SmartTemplate\Node\Tag\Tag::class, $document->children[0]);
        $this->assertEquals("tagname", $document->children[0]->name);
        $this->assertEquals("namespace", $document->children[0]->namespace);
    }

    public function testExpressionTag()
    {
        Compiler::setParserOptions([ 'resolver' => $this->resolver ]);
        $parser = Compiler::getParser();

        $document = $parser->parse("{\$variable}");
        $this->assertEquals("<?php echo \$variable; ?>", $document->getPhpCode());

        $document = $parser->parse("{\$variable ? '1' : '2'}");
        $this->assertEquals("<?php echo \$variable ? '1' : '2'; ?>", $document->getPhpCode());
    }

    public function testExpressionArgument()
    {
        Compiler::setParserOptions([ 'resolver' => $this->resolver ]);
        $parser = Compiler::getParser();

        $document = $parser->parse("<p>{translate 'The message'}</p>");

        $this->assertEquals("<p><?php echo _('The message'); ?></p>", $document->getPhpCode());
    }

    public function testInclude()
    {
        Compiler::setParserOptions([ 'resolver' => $this->resolver ]);
        $parser = Compiler::getParser();

        Compiler::setWorkingDir(dirname(__DIR__));

        $document = $parser->parse("<p>{include 'data/included.phtml'}</p>");
        $this->assertEquals("<p><?php include \"" . dirname(__DIR__) . "/data/included.phtml\"; ?></p>", $document->getPhpCode());

        $document = $parser->parse("<p>{include 'data/included.tpl'}</p>");
        $this->assertEquals("<p>I am included\n</p>", $document->getPhpCode());
    }

    public function testLiteral()
    {
        Compiler::setParserOptions([ 'resolver' => $this->resolver ]);
        $parser = Compiler::getParser();

        $document = $parser->parse("{literal}This is {some} literal content{/literal}");
        $this->assertEquals("This is {some} literal content", $document->getPhpCode());
    }

    public function testCapture()
    {
        Compiler::setParserOptions([ 'resolver' => $this->resolver ]);
        $parser = Compiler::getParser();

        $document = $parser->parse("{capture 'name'}This is captured content{/capture}");
        $this->assertEquals("<?php \$this->capture(\"name\"); ?>This is captured content<?php \$this->endCapture(); ?>", $document->getPhpCode());
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

    public function testUndefinedTags()
    {
        $parser = Compiler::getParser();
        $document = $parser->parse('{parent}{child}{child}{/parent}');

        $parent = $document->children[0];

        $this->assertInstanceOf(\TemplateKit\SmartTemplate\Node\Tag\PhpTemplate::class, $parent);
        $this->assertCount(2, $parent->children);
    }
}
