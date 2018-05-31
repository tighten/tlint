<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class NoUnusedImports extends BaseLinter
{
    protected $description = 'There should be no unused imports.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $useStatements = [];
        $used = [];

        $useStatementsVisitor = new FindingVisitor(function (Node $node) use (&$useStatements, &$used) {
            if ($node instanceof Node\Stmt\UseUse) {
                $useStatements[] = $node;
            } elseif ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Name) {
                $used[] = $node->class->toString();
            } elseif ($node instanceof Node\Expr\StaticCall
                && property_exists($node, 'class')
                && method_exists($node->class, 'toString')
            ) {
                $used[] = $node->class->toString();
            } elseif ($node instanceof Node\Expr\ClassConstFetch) {
                $used[] = $node->class->toString();
            } elseif ($node instanceof Node\Stmt\Class_
                && property_exists($node, 'extends')
                && method_exists($node->extends, 'toString')
            ) {
                $used[] = $node->extends->toString();
            } elseif ($node instanceof Node\Param
                && property_exists($node, 'type')
                && $node->type instanceof Node\Name
            ) {
                $used[] = $node->type->toString();
            } elseif ($node instanceof Node\Stmt\Catch_ && property_exists($node, 'types')) {
                foreach ($node->types as $type) {
                    $used[] = $type->toString();
                }
            } elseif ($node instanceof Node\Expr\Instanceof_) {
                $used[] = $node->class->toString();
            } elseif ($node instanceof Node\Stmt\TraitUse) {
                foreach ($node->traits as $name) {
                    $used[] = $name->toString();
                }
            }

            return false;
        });

        $traverser->addVisitor($useStatementsVisitor);

        $traverser->traverse($parser->parse($this->code));

        if (! empty($useStatements)) {
            $unusedImports = array_filter($useStatements, function (UseUse $node) use ($used) {
                return ! in_array(last(explode('\\', $node->name->toString())) ?? $node->name->toString(), $used);
            });

            return $unusedImports;
        }

        return [];
    }
}
