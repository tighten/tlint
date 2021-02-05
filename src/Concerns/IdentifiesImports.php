<?php

namespace Tighten\Concerns;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;

trait IdentifiesImports
{
    private function getUnusedImportLines(array $stmts)
    {
        return array_map(function (Node $node) {
            return $node->getLine();
        }, $this->getUnusedImportNodes($stmts));
    }

    private function getUnusedImportNodes(array $stmts)
    {
        $traverser = new NodeTraverser;

        $groupUseStatements = [];
        $useStatements = [];
        $used = [];

        $useStatementsVisitor = new FindingVisitor(function (Node $node) use (
            &$useStatements,
            &$used,
            &$groupUseStatements
        ) {
            if ($node instanceof Stmt\GroupUse) {
                foreach ($node->uses as $use) {
                    $groupUseStatements[] = $use->name->toString();
                }
            }

            if ($node instanceof Node\Stmt\UseUse) {
                if (! in_array($node->name->toString(), $groupUseStatements)) {
                    $useStatements[] = $node;
                }
            } elseif ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Name) {
                $used[] = $node->class->toString();
            } elseif ($node instanceof Node\Expr\StaticCall && $node->class instanceof Node\Name) {
                $used[] = $node->class->toString();
            } elseif ($node instanceof Node\Expr\StaticPropertyFetch && $node->class instanceof Node\Name) {
                $used[] = $node->class->toString();
            } elseif ($node instanceof Node\Expr\ClassConstFetch && $node->class instanceof Node\Name) {
                $used[] = $node->class->toString();
            } elseif ($node instanceof Node\Stmt\Class_) {
                if ($node->extends instanceof Node\Name) {
                    $used[] = $node->extends->toString();
                }

                if (property_exists($node, 'implements')) {
                    array_map(function ($implemented) use (&$used) {
                        $used[] = $implemented->toString();
                    }, $node->implements);
                }
            } elseif ($node instanceof Node\Stmt\Interface_) {
                foreach ($node->extends as $name) {
                    if ($name instanceof Node\Name) {
                        $used[] = $name->toString();
                    }
                }
            } elseif (
                ($node instanceof Node\Param || $node instanceof Node\Stmt\Property)
                && property_exists($node, 'type')
            ) {
                $type = $node->type;
                if ($type instanceof Node\NullableType) {
                    $type = $type->type;
                }
                if ($type instanceof Node\Name) {
                    $used[] = $type->toString();
                }
            } elseif ($node instanceof Node\Stmt\Catch_ && property_exists($node, 'types')) {
                foreach ($node->types as $type) {
                    $used[] = $type->toString();
                }
            } elseif ($node instanceof Node\Expr\Instanceof_ && $node->class instanceof Node\Name) {
                $used[] = $node->class->toString();
            } elseif ($node instanceof Node\Stmt\TraitUse) {
                foreach ($node->traits as $name) {
                    $used[] = $name->toString();
                }
            } elseif ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
                $used[] = $node->name->toString();
            } elseif ($node instanceof Node\Stmt\ClassMethod && property_exists($node, 'returnType')) {
                if ($node->returnType instanceof Node\Name) {
                    $used[] = $node->returnType->toString();
                } elseif (
                    $node->returnType instanceof Node\NullableType
                    && $node->returnType->type instanceof Node\Name
                ) {
                    $used[] = $node->returnType->type->toString();
                }
            } elseif ($node instanceof Node\Stmt\Function_ && property_exists($node, 'returnType')) {
                if ($node->returnType instanceof Node\Name) {
                    $used[] = $node->returnType->toString();
                } elseif (
                    $node->returnType instanceof Node\NullableType
                    && $node->returnType->type instanceof Node\Name
                ) {
                    $used[] = $node->returnType->type->toString();
                }
            } elseif ($node instanceof Node\UnionType) {
                foreach ($node->types as $type) {
                    if ($type instanceof Node\Name) {
                        $used[] = (string) $type;
                    }
                }
            }

            return false;
        });

        $traverser->addVisitor($useStatementsVisitor);

        $traverser->traverse($stmts);

        return array_filter($useStatements, function (UseUse $node) use ($used) {
            $nodeName = $node->name->toString();

            if ($node->alias) {
                $nodeName = $node->alias->name;
            }

            $useStatementParts = explode('\\', $nodeName);
            $usedParts = array_map(function ($use) {
                return explode('\\', $use)[0] ?? $use;
            }, $used);

            return ! in_array(end($useStatementParts) ?? $nodeName, $usedParts);
        });
    }
}
