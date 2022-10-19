<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsMigrations;

class UseAnonymousMigrations extends BaseLinter
{
    use LintsMigrations;

    public const DESCRIPTION = 'Prefer anonymous class migrations.';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            return $node instanceof Class_
                && $node->extends->toString() === 'Migration'
                && $node->name;
        };
    }
}
