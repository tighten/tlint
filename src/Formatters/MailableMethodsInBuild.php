<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\MailableMethodsInBuild as Linter;

class MailableMethodsInBuild extends BaseFormatter
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
        $oldTokens = $parser->getTokens();

        $newStmts = $traverser->traverse($oldStmts);

        $constructorVisitor = $this->constructorVisitor();

        $traverser = new NodeTraverser;
        $traverser->addVisitor($constructorVisitor);
        $newStmts = $traverser->traverse($newStmts);

        $buildVisitor = $this->buildVisitor($constructorVisitor->getMoveStmts());

        $traverser = new NodeTraverser;
        $traverser->addVisitor($buildVisitor);
        $newStmts = $traverser->traverse($newStmts);

        return preg_replace('/\r?\n/', PHP_EOL, (new Standard)->printFormatPreserving($newStmts, $oldStmts, $oldTokens));
    }

    private function constructorVisitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract
        {
            private bool $extendsMailable = false;

            public $moveStmts = [];

            public function getMoveStmts()
            {
                return $this->moveStmts;
            }

            public function beforeTraverse(array $nodes)
            {
                $this->extendsMailable = false;

                return null;
            }

            public function enterNode(Node $node): Node|int|null
            {
                if ($node instanceof Node\Stmt\Class_
                    && ! empty($node->extends)
                    && $node->extends->toString() === 'Mailable'
                ) {
                    $this->extendsMailable = true;
                }

                if (! $this->extendsMailable) {
                    return null;
                }

                if (! $node instanceof Node\Stmt\ClassMethod) {
                    return null;
                }

                if ($node->name->name !== '__construct') {
                    return null;
                }

                collect($node->getStmts())->each(function ($stmt) {
                    if (! $stmt instanceof Node\Stmt\Expression) {
                        return;
                    }

                    if (! $stmt->expr instanceof Node\Expr\MethodCall) {
                        return;
                    }

                    if ($stmt->expr->var->name !== 'this') {
                        return;
                    }

                    $this->moveStmts[] = $stmt;
                });

                if (empty($this->moveStmts)) {
                    return null;
                }

                $node->stmts = array_values(array_filter($node->getStmts(), function ($stmt) {
                    return ! in_array($stmt, $this->moveStmts, true);
                }));

                return $node;
            }
        };
    }

    private function buildVisitor(array $moveStmts): NodeVisitorAbstract
    {
        return new class($moveStmts) extends NodeVisitorAbstract
        {
            private bool $extendsMailable = false;

            private array $moveStmts = [];

            public function __construct($moveStmts)
            {
                $this->moveStmts = $moveStmts;
            }

            public function beforeTraverse(array $nodes)
            {
                $this->extendsMailable = false;

                return null;
            }

            public function enterNode(Node $node): Node|int|null
            {
                if (! $this->moveStmts) {
                    return null;
                }

                if ($node instanceof Node\Stmt\Class_
                    && ! empty($node->extends)
                    && $node->extends->toString() === 'Mailable'
                ) {
                    $this->extendsMailable = true;
                }

                if (! $this->extendsMailable) {
                    return null;
                }

                if (! $node instanceof Node\Stmt\ClassMethod) {
                    return null;
                }

                if ($node->name->name !== 'build') {
                    return null;
                }

                $node->stmts = array_merge($this->moveStmts, $node->getStmts());

                return $node;
            }
        };
    }
}
