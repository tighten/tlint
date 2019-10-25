<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Parser;
use Tighten\BaseLinter;

class ArrayParametersOverViewWith extends BaseLinter
{
    protected $description = 'Prefer `view(..., [...])` over `view(...)->with(...)`.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\MethodCall
                && $node->var instanceof FuncCall
                && $node->var->name instanceof Node\Name
                && $node->var->name->toString() === 'view'
                && $node->name instanceof Node\Identifier
                && $node->name->toString() === 'with';
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
