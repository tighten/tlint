<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class NoStringInterpolationWithoutBraces extends BaseLinter
{
    protected $description = 'Never use string interpolation without braces';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) use ($parser) {
            if ($node instanceof Node\Scalar\Encapsed && $node->parts[0] instanceof Node\Expr\Variable) {
                $line = $this->getCodeLine($node->getStartLine());
                $name = $node->parts[0]->name;

                return ! str_contains($line, "{\${$name}}");
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
