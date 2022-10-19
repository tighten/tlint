<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsNonConfigFiles;

class UseConfigOverEnv extends BaseLinter
{
    use LintsNonConfigFiles;

    public const DESCRIPTION = 'Don’t use environment variables directly; instead, use them in config files and call config vars from code';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            return $node instanceof Node\Expr\FuncCall
                && $node->name instanceof Node\Name
                && $node->name->toString() === 'env';
        };
    }
}
