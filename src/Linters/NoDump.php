<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class NoDump extends BaseLinter
{
    public const description = 'There should be no calls to `dd()` or `dump()` or `var_dump()`';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof FuncCall && ! empty($node->name->parts) && in_array($node->name->parts[0], ['dd', 'dump', 'var_dump', 'ray'], true);
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
