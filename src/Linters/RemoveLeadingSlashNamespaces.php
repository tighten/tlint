<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class RemoveLeadingSlashNamespaces extends BaseLinter
{
    public const description = 'Prefer `Namespace\...` over `\Namespace\...`.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $classVisitor = new FindingVisitor(function (Node $node) {
            return (
                $node instanceof Node\Expr\New_
                || $node instanceof Node\Expr\StaticCall
                || $node instanceof Node\Expr\ClassConstFetch
            )
                && $node->class instanceof Node\Name
                && strpos($this->getCodeLine($node->getLine()), "\\" . $node->class->toString()) !== false
                && substr(rtrim($this->getCodeLine($node->getLine()), ',; '), -7) !== '::class';
        });

        $useStatementsVisitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Stmt\UseUse
                && $node->name instanceof Node\Name
                && strpos($this->getCodeLine($node->getLine()), "\\" . $node->name->toString()) !== false;
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
