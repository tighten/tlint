<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsControllers;
use Tighten\TLint\Linters\Concerns\LintsRoutesFiles;

class ViewWithOverArrayParameters extends BaseLinter
{
    use LintsControllers, LintsRoutesFiles {
        LintsControllers::appliesToPath as pathIsController;
        LintsRoutesFiles::appliesToPath as pathIsRoute;
    }

    public const DESCRIPTION = 'Prefer `view(...)->with(...)` over `view(..., [...])`.';

    public static function appliesToPath(string $path): bool
    {
        return static::pathIsController($path) || static::pathIsRoute($path);
    }

    protected function visitor(): Closure
    {
        return function (Node $node) {
            return $node instanceof FuncCall
                && $node->name instanceof Node\Name
                && $node->name->toString() === 'view'
                && ($node->args[1]->value ?? null) instanceof Array_;
        };
    }
}
