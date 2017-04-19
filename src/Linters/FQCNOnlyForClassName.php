<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\AbstractLinter;

class FQCNOnlyForClassName extends AbstractLinter
{
    public function lintDescription()
    {
        return 'Fully Qualified Class Names should only be used for accessing class names';
    }

    /**
     * @param Parser $parser
     * @return Node[]
     */
    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser();

        $fqcnNonClassName = new FindingVisitor(function (Node $node) {
            return ($node->name ?? null) !== 'class'
                && ($node instanceof Node\Expr\StaticPropertyFetch
                    || $node instanceof Node\Expr\StaticCall
                    || $node instanceof Node\Expr\ClassConstFetch
                    || $node instanceof Node\Expr\New_
                )
                // new $variable used to instantiate
                && !($node instanceof Node\Expr\New_ && $node->class instanceof Node\Expr\Variable)
                // anonymous class
                && !($node instanceof Node\Expr\New_ && $node->class instanceof Node\Stmt\Class_)
                && $node->class->isQualified();
        });

        $traverser->addVisitor($fqcnNonClassName);

        $traverser->traverse($parser->parse($this->code));

        return $fqcnNonClassName->getFoundNodes();
    }
}
