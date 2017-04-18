<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\AbstractLinter;

class RemoveLeadingSlashNamespaces extends AbstractLinter
{
    public function lintDescription()
    {
        return 'Prefer `Namespace\...` over `\Namespace\...`.';
    }

    /**
     * @param Parser $parser
     * @return Node[]
     */
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
                && strpos($this->getCodeLine($node->getLine()), "\\" . $node->name->toString()) !== false;
        });

        $staticCallVisitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\StaticCall
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
