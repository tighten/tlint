<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\Concerns\LintsMigrations;

class UseAnonymousMigrations extends BaseFormatter
{
    use LintsMigrations;

    public const DESCRIPTION = 'Prefer anonymous class migrations.';

    public function format(Parser $parser, Lexer $lexer)
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);

        $className = null;
        array_map(function (Node $node) use (&$className) {
            if (
                $node instanceof Class_
                    && $node->extends->toString() === 'Migration'
                    && $node->name
            ) {
                $className = $node->name->toString();
            }
        }, $traverser->traverse($parser->parse($this->code)));

        if ($className) {
            $this->code = str_replace("class {$className}", 'return new class', $this->code);
            $this->code = $this->str_replace_last('}', '};', $this->code);
        }

        return $this->code;
    }

    public function str_replace_last($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}
