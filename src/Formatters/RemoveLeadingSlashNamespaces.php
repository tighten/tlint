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
use PhpParser\PrettyPrinter\Standard;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\RemoveLeadingSlashNamespaces as Linter;

class RemoveLeadingSlashNamespaces extends BaseFormatter
{
    public const DESCRIPTION = Linter::DESCRIPTION;

    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return Linter::appliesToPath($path, $configPaths);
    }

    public function format(Parser $parser, Lexer $lexer): string
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloningVisitor());

        $oldStmts = $parser->parse($this->code);
        $newStmts = $traverser->traverse($oldStmts);

        $useStatementsVisitor = $this->useStatementVisitor($this->getCodeLines());
        $classVisitor = $this->classVisitor($this->getCodeLines());

        $traverser = new NodeTraverser();
        $traverser->addVisitor($useStatementsVisitor);
        $traverser->addVisitor($classVisitor);

        $traverser->traverse($newStmts);

        $replacements = $useStatementsVisitor->getReplacements() + $classVisitor->getReplacements();

        $imports = $classVisitor->getNewUseStatements();

        collect($replacements)->each(function ($replacement, $line) {
            $this->code = $this->replaceCodeLine($line, $replacement);
        });

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloningVisitor());

        $oldStmts = $parser->parse($this->code);
        $oldTokens = $lexer->getTokens();

        $newStmts = $traverser->traverse($oldStmts);

        $newStmts = collect($newStmts)->map(function($stmts) use ($imports) {
            if ($stmts instanceof Node\Stmt\Namespace_) {

                $originalImports = collect($stmts->stmts)->flatMap(function ($node) {
                    if ($node instanceof Node\Stmt\GroupUse || $node instanceof Node\Stmt\Use_) {
                        return collect($node->uses)->map(fn ($use) => $use->name->toString())->toArray();
                    }
                });

                $imports = collect($imports)->diff($originalImports)->map(function($import) {
                    return new Node\Stmt\Use_([new Node\Stmt\UseUse(new Node\Name($import))]);
                })->toArray();

                $stmts->stmts = [...$imports, ...$stmts->stmts];
            }

            return $stmts;
        })->filter()->toArray();

        return (new Standard)->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
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

            public $useStatements = [];

            private $codeLines = [];

            public function __construct($codeLines)
            {
                $this->codeLines = $codeLines;
            }

            public function getReplacements()
            {
                return $this->replaceLines;
            }

            public function getNewUseStatements()
            {
                return $this->useStatements;
            }

            public function beforeTraverse(array $nodes)
            {
                $this->useStatements = [];

                return null;
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

                if (Str::contains($this->codeLines[$node->getLine() - 1], '\\' . $node->class->toString() . '::class')
                    && ! Str::contains($this->codeLines[$node->getLine() - 1], 'factory')
                ) {
                    return null;
                }

                Str::matchAll('/(' . preg_quote('\\' . $node->class->toString()) . ')[(:@]{1}/', $this->codeLines[$node->getLine() - 1])
                    ->each(function($match) use ($node) {
                        $this->useStatements[] = $node->class->toString();
                        $this->replaceLines[$node->getLine()] = Str::replace($match, $node->class->toString(), $this->codeLines[$node->getLine() - 1]);
                    });

                return $node;
            }
        };
    }
}
