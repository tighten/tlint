<?php

namespace Tighten\Formatters;

use PhpParser\Lexer;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use Tighten\BaseFormatter;

class ExcessSpaceBetweenAndAfterImports extends BaseFormatter
{
    public const description = 'Removes excess newlines around use statements.';

    public function format(Parser $parser, Lexer $lexer)
    {
        $traverser = new NodeTraverser;

        $traverser->addVisitor(new CloningVisitor);

        $oldStmts = $parser->parse($this->code);

        $newStmts = $traverser->traverse($oldStmts);

        $lastUseStatementLine = null;
        $firstUseStatementLine = null;
        if ($newStmts[0] instanceof Namespace_) {
            $firstUseStatementLine = $this->getFirstUseStatementLine($newStmts[0]->stmts);
            $lastUseStatementLine = $this->getLastUseStatementLine($newStmts[0]->stmts);
        } elseif (count($newStmts) && ($newStmts[0] instanceof Use_ || $newStmts[0] instanceof UseUse)) {
            $firstUseStatementLine = $this->getFirstUseStatementLine($newStmts);
            $lastUseStatementLine = $this->getLastUseStatementLine($newStmts);
        }

        $haveRunIntoCodeAfterUseStatements = false;
        if ($lastUseStatementLine && $firstUseStatementLine) {
            $codeLinesWithoutExcessSpace = [];

            foreach ($this->codeLines as $line => $codeLine) {
                if ($haveRunIntoCodeAfterUseStatements) {
                    $codeLinesWithoutExcessSpace[] = $codeLine;
                    continue;
                }

                // Before use statement range.
                if ($line + 1 < $firstUseStatementLine) {
                    $codeLinesWithoutExcessSpace[] = $codeLine;
                } // First use statement.
                elseif ($line + 1 === $firstUseStatementLine) {
                    $codeLinesWithoutExcessSpace[] =
                        $codeLine
                        // Special case when there is only 1 use statement.
                        . ($firstUseStatementLine === $lastUseStatementLine ? "\n" : '');
                } // Last use statement.
                elseif ($line + 1 === $lastUseStatementLine) {
                    $codeLinesWithoutExcessSpace[] = $codeLine . "\n";
                } // After use statement range.
                elseif ($line + 1 > $lastUseStatementLine) {
                    if (trim($codeLine) !== '') {
                        $haveRunIntoCodeAfterUseStatements = true;
                        $codeLinesWithoutExcessSpace[] = $codeLine;
                    }
                } else {
                    // Use statements.
                    if (trim($codeLine) !== '') {
                        $codeLinesWithoutExcessSpace[] = $codeLine;
                    }
                }
            }

            return implode("\n", $codeLinesWithoutExcessSpace);
        }

        return $this->code;
    }

    private function getLastUseStatementLine(array $stmts)
    {
        $lastUseStatementEndLine = 0;

        foreach ($stmts as $index => $newStmt) {
            if ($newStmt instanceof Use_ || $newStmt instanceof UseUse) {
                $lastUseStatementEndLine = $newStmt->getEndLine();
            }
        }

        return $lastUseStatementEndLine;
    }

    private function getFirstUseStatementLine(array $stmts)
    {
        foreach ($stmts as $index => $newStmt) {
            if ($newStmt instanceof Use_ || $newStmt instanceof UseUse) {
                return $newStmt->getStartLine();
            }
        }

        return 0;
    }
}
