<?php

namespace Tighten\Linters;

use Illuminate\View\Compilers\Concerns\CompilesConditionals;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\AbstractLinter;

class UseAuthHelperOverFacade extends AbstractLinter
{
    use CompilesConditionals;

    public function lintDescription()
    {
        return 'Prefer the `auth()` helper function over the `Auth` Facade.';
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
                && in_array($node->class->toString(), ['Auth', 'Illuminate\Support\Facades\Auth']);
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
