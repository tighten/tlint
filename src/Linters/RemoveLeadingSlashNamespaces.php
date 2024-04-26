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
        $traverser = new NodeTraverser;

        $classVisitor = new FindingVisitor(function (Node $node) {
            if (! $node instanceof Node\Expr\New_
                && ! $node instanceof Node\Expr\StaticCall
                && ! $node instanceof Node\Expr\ClassConstFetch
            ) {
                return false;
            }

            if (! $node->class instanceof Node\Name) {
                return false;
            }

            if (Str::contains($this->codeLines[$node->getLine() - 1], '\\' . $node->class->toString() . '::class')
                && ! Str::contains($this->codeLines[$node->getLine() - 1], 'factory')
            ) {
                return false;
            }

            return Str::matchAll('/(' . preg_quote('\\' . $node->class->toString()) . ')[(:@]{1}/', $this->codeLines[$node->getLine() - 1])
                ->isNotEmpty();
        });

        $useStatementsVisitor = new FindingVisitor(function (Node $node) {
            if (! $node instanceof Node\UseItem) {
                return false;
            }

            if (! $node->name instanceof Node\Name) {
                return false;
            }

            return Str::contains($this->codeLines[$node->getLine() - 1], '\\' . $node->name->toString());
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
