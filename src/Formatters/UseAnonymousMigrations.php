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

        $originalStatements = $parser->parse($this->code);
        $tokens = $lexer->getTokens();

        $statements = array_map(function (Node $node) {
            if (
                $node instanceof Class_
                    && $node->extends->toString() === 'Migration'
                    && $node->name
            ) {
                return new Return_(new New_($node));
            }

            return $node;
        }, $traverser->traverse($originalStatements));

        return (new Standard)->printFormatPreserving($statements, $originalStatements, $tokens);
    }
}
