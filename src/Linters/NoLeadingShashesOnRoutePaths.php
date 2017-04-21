<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\AbstractLinter;

class NoLeadingShashesOnRoutePaths extends AbstractLinter
{
    public function lintDescription()
    {
        return 'No leading slashes on route paths.';
    }

    /**
     * @param Parser $parser
     * @return Node[]
     */
    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser();

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\StaticCall
                && $node->class->toString() === 'Route'
                && isset($node->args[0])
                && $node->args[0]->value instanceof Node\Scalar\String_
                && strpos($node->args[0]->value->value, '/') === 0;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
