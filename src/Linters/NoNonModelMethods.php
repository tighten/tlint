<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Concerns\IdentifiesExtends;
use Tighten\Concerns\IdentifiesModelMethodTypes;

class NoNonModelMethods extends BaseLinter
{
    use IdentifiesModelMethodTypes;
    use IdentifiesExtends;

    protected $description = 'No non-model-specific methods in models'
        . ' (only relationships, scopes, accessors, mutators, boot)';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser();

        $visitor = new FindingVisitor(function (Node $node) {
            if ($this->extends($node, 'Model')) {
                /** @var Class_ $node */

                $methods = array_filter($node->stmts, function ($stmt) {
                    return $stmt instanceof ClassMethod;
                });

                foreach ($methods as $stmt) {
                    if (!$this->isAccessorMethod($stmt)
                        && !$this->isMutatorMethod($stmt)
                        && !$this->isBootMethod($stmt)
                        && !$this->isScopeMethod($stmt)
                        && !$this->isRelationshipMethod($stmt)
                    ) {
                        return true;
                    }
                }
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
