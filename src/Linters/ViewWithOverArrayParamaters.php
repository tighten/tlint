<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class ViewWithOverArrayParamaters extends BaseLinter
{
    protected $description = 'Prefer `view(...)->with(...)` over `view(..., [...])`.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser();

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof FuncCall
                && $node->name instanceof Node\Name
                && $node->name->toString() === 'view'
                && ($node->args[1]->value ?? null) instanceof Array_;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
