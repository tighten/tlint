<?php

namespace Tighten\TLint\Linters;

use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\TLint\BaseLinter;

class RemoveLeadingSlashNamespaces extends BaseLinter
{
    public const DESCRIPTION = 'Prefer `Namespace\...` over `\Namespace\...`.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser();

        $classVisitor = new FindingVisitor(function (Node $node) {
            return (
                $node instanceof Node\Expr\New_
                || $node instanceof Node\Expr\StaticCall
                || $node instanceof Node\Expr\ClassConstFetch
            )
                && $node->class instanceof Node\Name
                && Str::contains($this->codeLines[$node->getLine() - 1], '\\' . $node->class->toString())
                && (! Str::contains($this->codeLines[$node->getLine() - 1], '\\' . $node->class->toString() . '::class')
                || Str::contains($this->codeLines[$node->getLine() - 1], 'factory'));
        });

        $useStatementsVisitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Stmt\UseUse
                && $node->name instanceof Node\Name
                && Str::contains($this->codeLines[$node->getLine() - 1], '\\' . $node->name->toString());
        });

        $traverser->addVisitor($classVisitor);
        $traverser->addVisitor($useStatementsVisitor);

        $traverser->traverse($parser->parse($this->code));

        return array_merge(
            $classVisitor->getFoundNodes(),
            $useStatementsVisitor->getFoundNodes()
        );
    }
}
