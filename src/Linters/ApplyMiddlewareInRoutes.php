<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsControllers;

class ApplyMiddlewareInRoutes extends BaseLinter
{
    use LintsControllers;

    public const DESCRIPTION = 'Apply middleware in routes (not controllers).';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            static $extendsController = false;

            if ($node instanceof Node\Stmt\Class_
                && $node->extends instanceof Node\Name
                && $node->extends->toString() === 'Controller') {
                $extendsController = true;
            }

            if ($extendsController) {
                return $node instanceof Node\Expr\MethodCall
                    && $node->var instanceof Node\Expr\Variable
                    && $node->var->name === 'this'
                    && $node->name instanceof Node\Identifier
                    && $node->name->name === 'middleware';
            }

            return false;
        };
    }
}
