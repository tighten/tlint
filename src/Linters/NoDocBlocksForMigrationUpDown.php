<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsMigrations;

class NoDocBlocksForMigrationUpDown extends BaseLinter
{
    use LintsMigrations;

    public const DESCRIPTION = 'Remove doc blocks from the up and down method in migrations.';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            return $node instanceof Node\Stmt\ClassMethod
                && in_array($node->name, ['up', 'down'])
                && (bool) $node->getDocComment();
        };
    }
}
