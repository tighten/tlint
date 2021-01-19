<?php

namespace Tighten\Formatters;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseFormatter;
use Tighten\Linters\Concerns\LintsMigrations;

class NoDocBlocksForMigrationUpDown extends BaseFormatter
{
    use LintsMigrations;

    public const description = 'Removes doc blocks from the up and down method in migrations.';

    public function format(Parser $parser, Lexer $lexer)
    {
        $oldStmts = $parser->parse($this->code);

        $updowndocblocklines = $this->getUpDownMigrationDocBlockLines($oldStmts);

        $codeLinesWithoutUpDownMigrationDocBlocks = [];

        foreach ($this->codeLines as $line => $codeLine) {
            if (! in_array($line + 1, $updowndocblocklines)) {
                $codeLinesWithoutUpDownMigrationDocBlocks[] = $codeLine;
            }
        }

        return implode("\n", $codeLinesWithoutUpDownMigrationDocBlocks);
    }

    private function getUpDownMigrationDocBlockLines(array $stmts): array
    {
        $traverser = new NodeTraverser;
        $lines = [];

        $visitor = new FindingVisitor(function (Node $node) use (&$lines) {
            if (
                $node instanceof Node\Stmt\ClassMethod
                && in_array($node->name, ['up', 'down'])
                && (bool) $node->getDocComment()
            ) {
                $lines = array_merge($lines, range($node->getDocComment()->getStartLine(), $node->getDocComment()->getEndLine()));
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($stmts);

        return $lines;
    }
}
