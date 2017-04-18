<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\AbstractLinter;

class UseConfigOverEnv extends AbstractLinter
{
    public function lintDescription()
    {
        return 'Don’t use environment variables directly; instead,'
            . ' use them in config files and call config vars from code';
    }

    /**
     * @param Parser $parser
     * @return Node[]
     */
    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser();

        $envUsages = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\FuncCall
                && $node->name->toString() === 'env';
        });

        $traverser->addVisitor($envUsages);

        $traverser->traverse($parser->parse($this->code));

        return $envUsages->getFoundNodes();
    }
}
