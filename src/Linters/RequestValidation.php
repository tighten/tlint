<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Concerns\IdentifiesExtends;
use Tighten\Linters\Concerns\LintsControllers;

class RequestValidation extends BaseLinter
{
    use LintsControllers;
    use IdentifiesExtends;

    public const description = 'Use `request()->validate(...)` helper function or extract a FormRequest instead of using'
        . ' `$this->validate(...)` in controllers';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $isController = false;

        $visitor = new FindingVisitor(function (Node $node) use (&$isController) {
            if (! $isController && $this->extends($node, 'Controller')) {
                $isController = true;
            }

            return $node instanceof Node\Expr\MethodCall
                && $node->var instanceof Node\Expr\Variable
                && $node->var->name === 'this'
                && $node->name->name === 'validate';
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $isController ? $visitor->getFoundNodes() : [];
    }
}
