<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class QualifiedNamesOnlyForClassName extends BaseLinter
{
    public const description = 'Fully Qualified Class Names should only be used for accessing class names';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $fqcnExtends = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Stmt\Class_
                && ! empty($node->extends)
                && ($node->extends->isFullyQualified() || $node->extends->isQualified());
        });

        $fqcnNonClassName = new FindingVisitor(function (Node $node) {
            return ($node->name->name ?? null) !== 'class'
                && ($node instanceof Node\Expr\StaticPropertyFetch
                    || $node instanceof Node\Expr\StaticCall
                    || $node instanceof Node\Expr\ClassConstFetch
                    || $node instanceof Node\Expr\New_
                )
                // new $variable used to instantiate
                && ! ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Expr\Variable)
                // anonymous class
                && ! ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Stmt\Class_)
                && ($node->class instanceof Node\Name && $node->class->isQualified());
        });

        $fqcnTrait = new FindingVisitor(function (Node $node) {
            if ($node instanceof Node\Stmt\TraitUse) {
                foreach ($node->traits as $trait) {
                    if ($trait->isQualified() || $trait->isFullyQualified()) {
                        return true;
                    }
                }
            }

            return false;
        });

        $traverser->addVisitor($fqcnExtends);
        $traverser->addVisitor($fqcnNonClassName);
        $traverser->addVisitor($fqcnTrait);

        $traverser->traverse($parser->parse($this->code));

        return array_merge(
            $fqcnExtends->getFoundNodes(),
            $fqcnNonClassName->getFoundNodes(),
            $fqcnTrait->getFoundNodes()
        );
    }
}
