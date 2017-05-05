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
}
