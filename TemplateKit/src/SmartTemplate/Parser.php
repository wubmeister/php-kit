<?php

namespace TemplateKit\SmartTemplate;

use Exception;
use TemplateKit\SmartTemplate\Node\Node;
use TemplateKit\SmartTemplate\Node\Content as ContentNode;
use TemplateKit\SmartTemplate\Node\Comment as CommentNode;
use TemplateKit\SmartTemplate\Node\Expression as ExpressionNode;
use TemplateKit\SmartTemplate\Node\Control as ControlNode;
use TemplateKit\SmartTemplate\Node\Tag\Tag;
use TemplateKit\SmartTemplate\Node\IfSequence;

class Parser
{
    protected $leftDelimiter = '{';
    protected $rightDelimiter = '}';
    protected $commentLeftDelimiter = '{*';
    protected $commentRightDelimiter = '*}';

    protected $contentTail;
    protected $resolver;

    public function __construct(array $options = [])
    {
        if (isset($options['leftDelimiter'])) $this->leftDelimiter = $options['leftDelimiter'];
        if (strlen($this->leftDelimiter) > 1) throw new Exception('Left delimiter should be one character');
        if (isset($options['rightDelimiter'])) $this->rightDelimiter = $options['rightDelimiter'];
        if (strlen($this->rightDelimiter) > 1) throw new Exception('Right delimiter should be one character');
        if (isset($options['commentLeftDelimiter'])) $this->commentLeftDelimiter = $options['commentLeftDelimiter'];
        if (isset($options['commentRightDelimiter'])) $this->commentRightDelimiter = $options['commentRightDelimiter'];

        if (isset($options['resolver'])) $this->resolver = $options['resolver'];
        else throw new Exception('Parser options must at least include a resolver instance');
    }

    protected function read($regex, $modifiers = '')
    {
        $regex = '/^' . $regex . '/' . $modifiers;
        if (preg_match($regex, $this->contentTail, $match)) {
            $this->contentTail = substr($this->contentTail, strlen($match[0]));
            return $match;
        }
        return null;
    }

    protected function phpizeExpressionPart($part)
    {
        return strtr($part, [
            'and' => '&&',
            'or' => '||'
        ]);
    }

    protected function readExpression()
    {
        $expression = new Expression();
        $rd = preg_quote($this->rightDelimiter, '/');
        while (strlen($this->contentTail) > 0) {
            // Read until string literal or right delimiter, whichever comes first
            if ($match = $this->read("([^\"'$rd]*)(\"|'|$rd)")) {
                // Found right delimiter
                if (!empty($match[1])) $expression->append($match[1]);
                if ($match[2] == $this->rightDelimiter) {
                    break;
                }
                $stringLit = $match[2];
                // Consume string literal
                if ($match = $this->read("([^{$match[2]}\\\\]*(\\\\.)?)+{$match[2]}")) {
                    $expression->appendString($stringLit.$match[0]);
                } else {
                    throw new Exception('Syntax error');
                }
            } else {
                throw new Exception('Syntax error');
            }
        }

        return $expression;
    }

    public function parse(string $content)
    {
        $ld = preg_quote($this->leftDelimiter, '/');
        $rd = preg_quote($this->rightDelimiter, '/');
        $cld = preg_quote($this->commentLeftDelimiter, '/');

        $this->contentTail = $content;
        $root = new Node();
        $nodes = [ $root ];
        $topNode = $root;

        if ($match = $this->read("[^$ld]+")) {
            $textContent = $match[0];
            if (!empty($textContent)) {
                $topNode->appendChild(new ContentNode($textContent));
            }
        }

        while (strlen($this->contentTail) > 0) {
            if ($this->read("$cld")) {
                $pos = strpos($this->contentTail, $this->commentRightDelimiter);
                if ($pos === false) {
                    $comment = $this->contentTail;
                    $this->contentTail = '';
                } else {
                    $comment = substr($this->contentTail, 0, $pos);
                    $this->contentTail = substr($this->contentTail, $pos + strlen($this->commentRightDelimiter));
                }
                $topNode->appendChild(new CommentNode($comment));
            } else if ($this->read("$ld")) {
                if (strlen($this->contentTail) == 0) break;

                if ($this->contentTail[0] != '/' && !ctype_alpha($this->contentTail[0])) {
                    $expression = $this->readExpression();
                    if (!empty($expression)) {
                        $topNode->appendChild(new ExpressionNode($expression));
                    }
                } else {

                    $match = $this->read("((\/?)([^\s\/$rd]+))\s*");
                    $endTag = !empty($match[2]);
                    $tagName = $match[3];

                    if ($tagName == 'literal') {
                        $this->read("[^$rd]*$rd");
                        $endLiteral = "{$this->leftDelimiter}/literal{$this->rightDelimiter}";
                        $pos = strpos($this->contentTail, $endLiteral);
                        if ($pos === false) {
                            throw new Exception('Unclosed tag \'literal\'');
                        }
                        $textContent = substr($this->contentTail, 0, $pos);
                        $this->contentTail = substr($this->contentTail, $pos + strlen($endLiteral));
                        if (!empty($textContent)) {
                            $topNode->appendChild(new ContentNode($textContent));
                        }
                    } else if (in_array($tagName, [ 'else', 'elseif', 'for', 'if', 'while' ])) {
                        $expression = $this->readExpression();
                        if ($endTag) {
                            while (count($nodes) > 0) {
                                $top = array_pop($nodes);
                                if (($tagName == 'if' && in_array($top->name, [ 'else', 'elseif', 'if' ])) || ($tagName != 'if' && $top->name == $tagName)) {
                                    break;
                                }
                            }
                            $topNode = end($nodes);
                            if ($topNode->name == 'ifsequence') {
                                array_pop($nodes);
                                $topNode = end($nodes);
                            }
                        } else {
                            if ($tagName == 'if') {
                                $node = new IfSequence();
                                $topNode->appendChild($node);
                                $nodes[] = $node;
                                $topNode = $node;
                            } else if ($tagName == 'else' || $tagName == 'elseif') {
                                while (count($nodes) > 0) {
                                    array_pop($nodes);
                                    $topNode = end($nodes);
                                    if ($topNode->name == 'ifsequence') {
                                        break;
                                    }
                                }
                                if (!$topNode) {
                                    throw new Exception('Unexpected ' . $tagName);
                                }
                            }
                            $node = new ControlNode($tagName, $expression);
                            $topNode->appendChild($node);
                            $nodes[] = $node;
                            $topNode = $node;
                        }
                    } else {
                        $selfClosing = false;
                        $attributes = [];

                        while (strlen($this->contentTail) > 0) {
                            if ($match = $this->read("(\/\s*)?$rd")) {
                                $selfClosing = !empty($match[1]);
                                break;
                            }

                            if ($match = $this->read("[a-zA-Z][a-zA-Z0-9\-_]*")) {
                                // $match = $this->read("[^=\s\/$rd]+");
                                $attributeName = $match[0];
                                if ($match = $this->read("=(\"[^\"]+\"|'[^']+'|[^\s$rd]+)")) {
                                    $attributeValue = $match[0] != '"' && $match[0] != '\'' ? new ExpressionAttribute($match[1]) : trim($match[1], '"\'');
                                } else {
                                    $attributeValue = true;
                                }
                                $attributes[$attributeName] = $attributeValue;
                                $this->read("\s*");
                            } else {
                                $attributes['expression'] = $this->readExpression();
                                break;
                            }
                        }

                        if ($endTag) {
                            while (count($nodes) > 0) {
                                $top = array_pop($nodes);
                                if ($top->name == $tagName) {
                                    break;
                                }
                            }
                            $topNode = end($nodes);
                        } else {
                            $tag = Tag::factory($tagName, $attributes, $this->resolver); // new Tag($tagName, $attributes);
                            $topNode->appendChild($tag);
                            if (!$selfClosing && !$tag->isSelfClosing) {
                                $nodes[] = $tag;
                                $topNode = $tag;
                            }
                        }
                    }
                }
            } else {
                break;
            }

            if ($match = $this->read("[^$ld]+")) {
                $textContent = $match[0];
                if (!empty($textContent)) {
                    $topNode->appendChild(new ContentNode($textContent));
                }
            }
        }

        return $root;
    }
}
