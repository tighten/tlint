<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Linters\Concerns\LintsNonConfigFiles;

class UseConfigOverEnv extends BaseLinter
{
    use LintsNonConfigFiles;

    public const description = 'Don’t use environment variables directly; instead,'
        . ' use them in config files and call config vars from code';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $envUsages = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\FuncCall
                && $node->name instanceof Node\Name
                && $node->name->toString() === 'env';
        });

        $traverser->addVisitor($envUsages);

        $traverser->traverse($parser->parse($this->code));

        return $envUsages->getFoundNodes();
    }
}
