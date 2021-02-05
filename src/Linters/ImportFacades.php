<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Concerns\IdentifiesFacades;

class ImportFacades extends BaseLinter
{
    use IdentifiesFacades;

    public const description = "Import facades (don't use aliases).";

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
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
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
