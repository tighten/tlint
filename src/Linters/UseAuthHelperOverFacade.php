<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;

class UseAuthHelperOverFacade extends BaseLinter
{
    public const DESCRIPTION = 'Prefer the `auth()` helper function over the `Auth` Facade.';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            static $usesAuthFacade = false;

            if ($node instanceof Node\Stmt\UseUse && $node->name instanceof Node\Name) {
                if ($node->name->toString() === 'Illuminate\Support\Facades\Auth') {
                    $usesAuthFacade = true;
                }
            }

            return $node instanceof Node\Expr\StaticCall
                // use Illuminate\Support\Facades\Auth and calling Auth::
                && (($usesAuthFacade && $node->class instanceof Node\Name && $node->class->toString() === 'Auth')
                    // FQCN usage
                    || (
                        $node->class instanceof Node\Name
                        && $node->class->toString() === 'Illuminate\Support\Facades\Auth'
                    ))
                && ($node->class instanceof Node\Name && $node->name->name !== 'routes');
        };
    }
}
