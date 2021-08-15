<?php

namespace Tighten\TLint\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Concerns\IdentifiesFacades;

class FullyQualifiedFacades extends BaseLinter
{
    use IdentifiesFacades;

    public const description = "Import facades using their full namespace.";

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $groupUseStatements = [];

        $visitor = new FindingVisitor(function (Node $node) use (&$groupUseStatements) {
            /**
             * Get array of group use statement classes
             * Illuminate\Support\Facades\{Config, Hash} => ['Config', 'Hash']
             */
            if ($node instanceof Node\Stmt\GroupUse) {
                foreach ($node->uses as $use) {
                    $groupUseStatements[] = $use->name->toString();
                }
            }

            if ($node instanceof Node\Stmt\UseUse) {
                if (! in_array($node->name->toString(), $groupUseStatements)) {
                    return in_array($node->name->toString(), array_keys(static::$aliases));
                }
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
