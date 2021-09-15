<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Node;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\NodeTraverser;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use Tighten\TLint\BaseFormatter;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\NodeVisitor\CloningVisitor;
use Tighten\TLint\Linters\Concerns\LintsMigrations;

class UseAnonymousMigrations extends BaseFormatter
{
    use LintsMigrations;

    public const description = 'Prefer anonymous class migrations.';

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
            $this->code = $this->str_lreplace('}', '};', $this->code);
        }

        return $this->code;
    }

    public function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}
