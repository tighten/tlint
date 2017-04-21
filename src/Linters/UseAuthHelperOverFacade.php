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
            static $usesAuthFacade = false;

            if ($node instanceof Node\Stmt\UseUse && $node->name instanceof Node\Name) {
                if ($node->name->toString() === 'Illuminate\Support\Facades\Auth') {
                    $usesAuthFacade = true;
                }
            }

            return $node instanceof Node\Expr\StaticCall
                // use Illuminate\Support\Facades\Auth and calling Auth::
                && (($usesAuthFacade && $node->class->toString() === 'Auth')
                    // FQCN usage
                    || (
                        $node->class instanceof Node\Name
                        && $node->class->toString() === 'Illuminate\Support\Facades\Auth'
                    ))
                && ($node->class instanceof Node\Name && $node->name !== 'routes');
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
