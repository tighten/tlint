<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Linters\Concerns\LintsRoutesFiles;

class NoLeadingSlashesOnRoutePaths extends BaseLinter
{
    use LintsRoutesFiles;

    public const description = 'No leading slashes on route paths.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\StaticCall
                && ($node->class instanceof Node\Name && $node->class->toString() === 'Route')
                && isset($node->args[0])
                && $node->args[0]->value instanceof Node\Scalar\String_
                && strpos($node->args[0]->value->value, '/') === 0
                && strlen($node->args[0]->value->value) > 1;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
