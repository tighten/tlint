<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;

class NoParensEmptyInstantiations extends BaseLinter
{
    public const DESCRIPTION = 'No parenthesis on empty instantiations';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            return $node instanceof Node\Expr\New_
                && empty($node->args)
                && $node->class instanceof Node\Name
                && strpos(
                    $this->getCodeLine($node->getAttributes()['startLine']),
                    'new ' . $node->class->toString() . '()'
                ) !== false;
        };
    }
}
