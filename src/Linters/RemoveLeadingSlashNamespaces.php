<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class RemoveLeadingSlashNamespaces extends BaseLinter
{
    protected $description = 'Prefer `Namespace\...` over `\Namespace\...`.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser();

        $instantiationsVisitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\New_
                && $node->class instanceof Node\Name
                && strpos($this->getCodeLine($node->getLine()), "\\" . $node->class->toString()) !== false;
        });

        $useStatementsVisitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Stmt\UseUse
                && $node->name instanceof Node\Name
                && strpos($this->getCodeLine($node->getLine()), "\\" . $node->name->toString()) !== false;
        });

        $staticCallVisitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\StaticCall
                && $node->class instanceof Node\Name
                && strpos($this->getCodeLine($node->getLine()), "\\" . $node->class->toString()) !== false;
        });

        $traverser->addVisitor($instantiationsVisitor);
        $traverser->addVisitor($useStatementsVisitor);
        $traverser->addVisitor($staticCallVisitor);

        $traverser->traverse($parser->parse($this->code));

        return array_merge(
            $instantiationsVisitor->getFoundNodes(),
            $useStatementsVisitor->getFoundNodes(),
            $staticCallVisitor->getFoundNodes()
        );
    }
}
