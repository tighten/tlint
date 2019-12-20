<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class NoParensEmptyInstantiations extends BaseLinter
{
    public const description = 'No parenthesis on empty instantiations';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\New_
                && empty($node->args)
                && $node->class instanceof Node\Name
                && strpos(
                    $this->getCodeLine($node->getAttributes()['startLine']),
                    "new " . $node->class->toString() . '()'
                ) !== false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
