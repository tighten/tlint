<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Name;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Concerns\IdentifiesFacades;

class ImportFacades extends BaseLinter
{
    use IdentifiesFacades;

    public const DESCRIPTION = "Import facades (don't use aliases).";

    protected function visitor(): Closure
    {
        return function (Node $node) {
            static $hasNamespace = false;

            if ($node instanceof Node\Stmt\Namespace_) {
                $hasNamespace = true;
            }

            static $useNames = [];
            static $useAliases = [];

            if ($node instanceof Node\Stmt\GroupUse) {
                foreach ($node->uses as $use) {
                    $useNames[] = Name::concat($node->prefix, $use->name)->toString();
                    $useAliases[] = $use->getAlias();
                }
            } elseif ($node instanceof Node\Stmt\UseUse) {
                $useNames[] = $node->name->toString();
                $useAliases[] = $node->getAlias();
            }

            return $node instanceof Node\Expr\StaticCall
                && $hasNamespace
                && $node->class instanceof Node\Name
                && in_array($node->class->toString(), array_keys(static::$aliases))
                && ! in_array($node->class->toString(), $useAliases)
                && ! in_array(static::$aliases[$node->class->toString()], $useNames);
        };
    }
}
