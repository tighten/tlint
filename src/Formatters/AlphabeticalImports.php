<?php

namespace Tighten\Formatters;

use PhpParser\Lexer;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\BaseFormatter;

class AlphabeticalImports extends BaseFormatter
{
    public const description = 'Alphabetizes import statements.';

    public function format(Parser $parser, Lexer $lexer)
    {
        $traverser = new NodeTraverser;

        $traverser->addVisitor(new CloningVisitor);
        $printer = new Standard;

        $oldStmts = $parser->parse($this->code);
        $oldTokens = $lexer->getTokens();

        $newStmts = $traverser->traverse($oldStmts);

        if ($newStmts[0] instanceof Namespace_) {
            $newStmts[0]->stmts = $this->transformToAlphabetized($newStmts[0]->stmts);
        } elseif (count($newStmts) && ($newStmts[0] instanceof Use_ || $newStmts[0] instanceof UseUse)) {
            $newStmts = $this->transformToAlphabetized($newStmts);
        }

        return $printer->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    }

    private function transformToAlphabetized(array $stmts)
    {
        $oldUseStmts = [];
        foreach ($stmts as $index => $newStmt) {
            if ($newStmt instanceof GroupUse) {
                return $stmts;
            }

            if ($newStmt instanceof Use_ || $newStmt instanceof UseUse) {
                $oldUseStmts[] = [
                    'index' => $index,
                    'stmt' => $newStmt,
                    'uses' => $newStmt->uses,
                    'type' => $newStmt->type,
                ];
            }
        }

        $orderedOldUseStmts = $oldUseStmts;
        uasort($orderedOldUseStmts, function ($a, $b) {
            if ($a['type'] !== $b['type'] && $a['type'] !== 0 && $b['type'] !== 0) {
                return $a['type'] <=> $b['type'];
            }

            return mb_strtolower($a['uses'][0]->name->toString()) <=> mb_strtolower($b['uses'][0]->name->toString());
        });
        $orderedOldUseStmts = array_values($orderedOldUseStmts);

        foreach ($stmts as $index => $newStmt) {
            if (isset($orderedOldUseStmts[$index]) && $orderedOldUseStmts[$index]['stmt'] instanceof Use_) {
                $stmts[$index] = new Use_(
                    $orderedOldUseStmts[$index]['stmt']->uses,
                    $orderedOldUseStmts[$index]['stmt']->type
                );
            } elseif (isset($orderedOldUseStmts[$index]) && $orderedOldUseStmts[$index]['stmt'] instanceof UseUse) {
                $stmts[$index] = new UseUse(
                    $orderedOldUseStmts[$index]['stmt']->name,
                    $orderedOldUseStmts[$index]['stmt']->alias,
                    $orderedOldUseStmts[$index]['stmt']->type
                );
            }
        }

        return $stmts;
    }
}
