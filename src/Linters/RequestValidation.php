<?php

namespace Tighten\TLint\Linters;

use PhpParser\Node;
use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Concerns\IdentifiesExtends;
use Tighten\TLint\Linters\Concerns\LintsControllers;

class RequestValidation extends BaseLinter
{
    use IdentifiesExtends;
    use LintsControllers;

    public const DESCRIPTION = 'Use `request()->validate(...)` helper function or extract a FormRequest instead of using `$this->validate(...)` in controllers';

    public function lint(Parser $parser)
    {
        $isController = false;

        $visitor = $this->visitUsing($parser, function (Node $node) use (&$isController) {
            if (! $isController && $this->extends($node, 'Controller')) {
                $isController = true;
            }

            return $node instanceof Node\Expr\MethodCall
                && $node->var instanceof Node\Expr\Variable
                && $node->var->name === 'this'
                && $node->name->name === 'validate';
        });

        return $isController ? $visitor->getFoundNodes() : [];
    }
}
