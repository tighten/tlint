<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;

class SpaceAfterSoleNotOperator extends BaseLinter
{
    public const DESCRIPTION = 'There should be a space after sole `!` operators';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            return $node instanceof Node\Expr\BooleanNot
                && strpos($this->getCodeLine($node->getLine()), '! ') === false;
        };
    }
}
