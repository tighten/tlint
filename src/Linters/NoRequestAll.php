<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class NoRequestAll extends BaseLinter
{
    public const description = 'No `request()->all()`. Use `request()->only(...)` to retrieve specific input values.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $traverser->addVisitor($visitor = new FindingVisitor(function (Node $node) {
            return (
                $node instanceof MethodCall
                && $node->var instanceof Variable
                && $node->var->name === 'request'
                && $node->name->name === 'all'
            ) || (
                $node instanceof MethodCall
                && $node->var instanceof FuncCall
                && $node->var->name->toString() === 'request'
                && $node->name->name === 'all'
            );
        }));

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
