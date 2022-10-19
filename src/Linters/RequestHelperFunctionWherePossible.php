<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsControllers;

class RequestHelperFunctionWherePossible extends BaseLinter
{
    use LintsControllers;

    public const DESCRIPTION = 'Use the request(...) helper function directly to access request values wherever possible';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            return $node instanceof Node\Expr\MethodCall
                && $node->name->name === 'get'
                && $node->var instanceof Node\Expr\FuncCall
                && $node->var->name->toString() === 'request';
        };
    }
}
