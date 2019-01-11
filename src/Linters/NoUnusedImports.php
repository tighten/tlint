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
            } elseif ($node instanceof Node\Stmt\Class_) {
                if (property_exists($node, 'extends') && method_exists($node->extends, 'toString')) {
                    $used[] = $node->extends->toString();
                }

                if (property_exists($node, 'implements')) {
                    array_map(function ($implemented) use (&$used) {
                        $used[] = $implemented->toString();
                    }, $node->implements);
                }
            } elseif ($node instanceof Node\Param
                && $node->type instanceof Node\Name
                && property_exists($node, 'type')
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
            } elseif ($node instanceof Node\Expr\FuncCall
                && $node->name instanceof Node\Name
            ) {
                $used[] = $node->name->toString();
            }

            return false;
        });

        $traverser->addVisitor($useStatementsVisitor);

        $traverser->traverse($parser->parse($this->code));

        if (! empty($useStatements)) {
            $unusedImports = array_filter($useStatements, function (UseUse $node) use ($used) {
                $nodeName = $node->name->toString();
                if ($node->alias) {
                    $nodeName = $node->alias->name;
                }
                return ! in_array(last(explode('\\', $nodeName)) ?? $nodeName, $used);
            });

            return $unusedImports;
        }

        return [];
    }
}
