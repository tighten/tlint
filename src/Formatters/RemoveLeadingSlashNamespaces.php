<?php

namespace Tighten\TLint\Formatters;

use Illuminate\Support\Str;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\Concerns\LintsRoutesFiles;
use Tighten\TLint\Linters\RemoveLeadingSlashNamespaces as Linter;

class RemoveLeadingSlashNamespaces extends BaseFormatter
{
    use LintsRoutesFiles;

    public const DESCRIPTION = Linter::DESCRIPTION;

    public function format(Parser $parser, Lexer $lexer): string
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);

        $oldStmts = $parser->parse($this->code);
        $newStmts = $traverser->traverse($oldStmts);

        $useStatementsVisitor = $this->useStatementVisitor($this->getCodeLines());
        $classVisitor = $this->classVisitor($this->getCodeLines());

        $traverser = new NodeTraverser;
        $traverser->addVisitor($useStatementsVisitor);
        $traverser->addVisitor($classVisitor);

        $newStmts = $traverser->traverse($newStmts);

        $replacements = $useStatementsVisitor->getReplacements() + $classVisitor->getReplacements();

        collect($replacements)->each(function ($replacement, $line) {
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
                if (! $node instanceof Node\Stmt\UseUse) {
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

    private function classVisitor($codeLines): NodeVisitorAbstract
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
                if (! $node instanceof Node\Expr\New_
                    && ! $node instanceof StaticCall
                    && ! $node instanceof Node\Expr\ClassConstFetch
                ) {
                    return null;
                }

                if (! $node->class instanceof Node\Name) {
                    return null;
                }

                if (Str::contains($this->codeLines[$node->getLine() - 1], '\\' . $node->class->toString() . '::class')) {
                    return null;
                }

                if (Str::contains($this->codeLines[$node->getLine() - 1], '\\' . $node->class->toString())) {
                    $this->replaceLines[$node->getLine()] = Str::replace('\\' . $node->class->toString(), $node->class->toString(), $this->codeLines[$node->getLine() - 1]);
                }

                return $node;
            }
        };
    }
}
