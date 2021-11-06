<?php

namespace Tighten\TLint\Linters;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\TLint\BaseLinter;

class NoDump extends BaseLinter
{
    public const DESCRIPTION = 'There should be no calls to `dd()`, `dump()`, `ray()`, or `var_dump()`';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof FuncCall && ! empty($node->name->parts) && in_array($node->name->parts[0], ['dd', 'dump', 'var_dump', 'ray'], true)
                || $node instanceof Identifier && in_array($node->name, ['dump', 'dd'], true);
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
