<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Linters\Concerns\LintsControllers;

class RequestHelperFunctionWherePossible extends BaseLinter
{
    use LintsControllers;

    public const description = 'Use the request(...) helper function directly to access request values wherever possible';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\MethodCall
                && $node->name->name === 'get'
                && $node->var instanceof Node\Expr\FuncCall
                && $node->var->name->toString() === 'request';
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
