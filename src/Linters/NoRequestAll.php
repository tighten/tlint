<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Tighten\TLint\BaseLinter;

class NoRequestAll extends BaseLinter
{
    public const DESCRIPTION = 'No `request()->all()`. Use `request()->only(...)` to retrieve specific input values.';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            $isRequestCall = match (true) {
                $node instanceof MethodCall
                    && $node->var instanceof Node\Expr\Variable
                    && $node->var->name === 'request',

                $node instanceof MethodCall
                    && $node->var instanceof Node\Expr\FuncCall
                    && $node->var->name instanceof Node\Name
                    && $node->var->name->toString() === 'request',

                $node instanceof StaticCall
                    && $node->class instanceof Node\Name
                    && $node->class->toString() === 'Request' => true,

                default => false,
            };

            return $isRequestCall
                && $node->name instanceof Node\Identifier
                && $node->name->name === 'all';
        };
    }
}
