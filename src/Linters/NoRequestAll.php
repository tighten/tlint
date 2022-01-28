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
            return ($node instanceof MethodCall && (string) $node->var->name === 'request')
                || ($node instanceof StaticCall && (string) $node->class === 'Request')
                && $node->name->name === 'all';
        };
    }
}
