<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\AbstractLinter;

class RequestHelperFunctionWherePossible extends AbstractLinter
{
    public function lintDescription()
    {
        return 'Use the request(...) helper function directly to access request values wherever possible';
    }

    /**
     * @param Parser $parser
     * @return Node[]
     */
    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser();

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\MethodCall
                && (is_string($node->var->name) ? $node->var->name : $node->var->name->toString()) === 'request'
                && $node->name === 'get';
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
