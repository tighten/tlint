<?php

namespace Tighten\Formatters;

use PhpParser\Lexer;
use PhpParser\Parser;
use Tighten\BaseFormatter;
use Tighten\Concerns\IdentifiesImports;

class UnusedImports extends BaseFormatter
{
    use IdentifiesImports;

    public const description = 'Removes unused import statements.';

    public function format(Parser $parser, Lexer $lexer)
    {
        $oldStmts = $parser->parse($this->code);

        $unusedUseStatementLines = $this->getUnusedImportLines($oldStmts);

        $codeLinesWithoutUnusedImportLines = [];

        foreach ($this->codeLines as $line => $codeLine) {
            if (! in_array($line + 1, $unusedUseStatementLines)) {
                $codeLinesWithoutUnusedImportLines[] = $codeLine;
            }
        }

        return implode("\n", $codeLinesWithoutUnusedImportLines);
    }
}
