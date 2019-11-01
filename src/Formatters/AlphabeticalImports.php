<?php

namespace Tighten\Formatters;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\BaseFormatter;

class AlphabeticalImports extends BaseFormatter
{
    protected $description = 'Orders imports alphabetically.';

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
        } elseif (count($newStmts) && $newStmts[0] instanceof Use_) {
            $newStmts = $this->transformToAlphabetized($newStmts);
        }

        return $printer->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    }

    private function transformToAlphabetized(array $stmts) {
        $oldUseStmts = [];
        foreach ($stmts as $index => $newStmt) {
            if ($newStmt instanceof Use_) {
                $oldUseStmts[] = [
                    'index' => $index,
                    'uses' => $newStmt->uses,
                ];
            }
        }

        $orderedOldUseStmts = collect($oldUseStmts)->sort(function ($a, $b) {
            return $a['uses'][0]->name->toString() <=> $b['uses'][0]->name->toString();
        })->values();

        foreach ($stmts as $index => $newStmt) {
            if ($newStmt instanceof Use_) {
                $stmts[$index] = new Use_($orderedOldUseStmts[$index]['uses']);
            }
        }

        return $stmts;
    }
}
