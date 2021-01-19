<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
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
            return ($node instanceof MethodCall && (string) $node->var->name === 'request')
                || ($node instanceof StaticCall && (string) $node->class === 'Request')
                && $node->name->name === 'all';
        }));

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
