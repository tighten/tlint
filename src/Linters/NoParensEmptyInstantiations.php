<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class NoParensEmptyInstantiations extends BaseLinter
{
    protected $description = 'No parenthesis on empty instantiations';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $useStatementsVisitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\New_
                && empty($node->args)
                && strpos(
                    $this->getCodeLine($node->getAttributes()['startLine']),
                    "new " . $node->class->toString() . '()'
                ) !== false;
        });

        $traverser->addVisitor($useStatementsVisitor);

        $traverser->traverse($parser->parse($this->code));

        return $useStatementsVisitor->getFoundNodes();
    }
}
