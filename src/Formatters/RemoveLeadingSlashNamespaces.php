<?php

namespace Tighten\TLint\Formatters;

use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\RemoveLeadingSlashNamespaces as Linter;

class RemoveLeadingSlashNamespaces extends BaseFormatter
{
    public const DESCRIPTION = Linter::DESCRIPTION;

    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return Linter::appliesToPath($path, $configPaths);
    }

    public function format(Parser $parser): string
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);

        $oldStmts = $parser->parse($this->code);
        $newStmts = $traverser->traverse($oldStmts);

        $useStatementsVisitor = $this->useStatementVisitor($this->getCodeLines());

        $traverser = new NodeTraverser;
        $traverser->addVisitor($useStatementsVisitor);

        $traverser->traverse($newStmts);

        collect($useStatementsVisitor->getReplacements())->each(function ($replacement, $line) {
            $this->code = $this->replaceCodeLine($line, $replacement);
        });

        return $this->code;
    }

    private function useStatementVisitor($codeLines): NodeVisitorAbstract
    {
        return new class($codeLines) extends NodeVisitorAbstract
        {
            public $replaceLines = [];

            private $codeLines = [];

            public function __construct($codeLines)
            {
                $this->codeLines = $codeLines;
            }

            public function getReplacements()
            {
                return $this->replaceLines;
            }

            public function enterNode(Node $node): Node|int|null
            {
                if (! $node instanceof Node\UseItem) {
                    return null;
                }

                if (! $node->name instanceof Node\Name) {
                    return null;
                }

                if (Str::contains($this->codeLines[$node->getLine() - 1], '\\' . $node->name->toString())) {
                    $this->replaceLines[$node->getLine()] = Str::replace('\\' . $node->name->toString(), $node->name->toString(), $this->codeLines[$node->getLine() - 1]);
                }

                return $node;
            }
        };
    }
}
