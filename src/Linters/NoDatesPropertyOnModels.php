<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Concerns\IdentifiesExtends;

class NoDatesPropertyOnModels extends BaseLinter
{
    use IdentifiesExtends;

    public const description = 'The `$dates` property was deprecated in Laravel 8. Use `$casts` instead.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $traverser->addVisitor($visitor = new FindingVisitor(function (Node $node) {
            static $model = false;

            if ($this->extendsAny($node, ['Model', 'Pivot', 'Authenticatable'])) {
                $model = true;
            }

            return $model && $node instanceof Property && (string) $node->props[0]->name === 'dates';
        }));

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
