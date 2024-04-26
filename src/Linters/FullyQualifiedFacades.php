<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Concerns\IdentifiesFacades;

class FullyQualifiedFacades extends BaseLinter
{
    use IdentifiesFacades;

    public const DESCRIPTION = 'Import facades using their full namespace.';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            static $groupUse = [];

            /**
             * Build an array of group use statement classes (groups use FQNs)
             * Illuminate\Support\Facades\{Config, Hash} => ['Config', 'Hash']
             */
            if ($node instanceof Node\Stmt\GroupUse) {
                foreach ($node->uses as $use) {
                    $groupUse[] = $use->name->toString();
                }
            }

            /**
             * Check if the node is a use statement and not a group use statement
             * Return if use statement is a facade or not
             */
            if ($node instanceof Node\UseItem && ! in_array($node->name->toString(), $groupUse)) {
                return in_array($node->name->toString(), array_keys(static::$aliases));
            }

            return false;
        };
    }
}
