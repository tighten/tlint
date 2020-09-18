<?php

namespace Tighten\Concerns;

use PhpParser\Node;

trait IdentifiesExtends
{
    private function extends(Node $node, string $class) : bool
    {
        return $node instanceof Node\Stmt\Class_
            && $node->extends
            && $node->extends->toString() === $class;
    }

    private function extendsAny(Node $node, array $classes) : bool
    {
        return $node instanceof Node\Stmt\Class_
            && $node->extends
            && in_array($node->extends->toString(), $classes, true);
    }
}
