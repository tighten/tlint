<?php

namespace Tighten\TLint\Formatters;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\ArrayParametersOverViewWith as Linter;

class ArrayParametersOverViewWith extends BaseFormatter
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

        $traverser = new NodeTraverser;
        $traverser->addVisitor($this->visitor());
        $newStmts = $traverser->traverse($newStmts);

        return preg_replace('/\r?\n/', PHP_EOL, (new Standard)->printFormatPreserving($newStmts, $oldStmts, $oldTokens));
    }

    private function visitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract
        {
            public $viewWith = [];

            public function beforeTraverse(array $nodes)
            {
                $this->viewWith = [];

                return null;
            }

            public function enterNode(Node $node): Node|int|null
            {
                $this->forViewChain($node, function ($node) {
                    $this->viewWith[] = [$node->getArgs()[0], $node->getArgs()[1]];
                });

                if (! $node instanceof Node\Expr\FuncCall) {
                    return null;
                }

                if (! $node->name instanceof Node\Name) {
                    return null;
                }

                if ($node->name->toString() !== 'view') {
                    return null;
                }

                if (! $this->viewWith) {
                    return null;
                }

                return new FuncCall(
                    new Name('view'),
                    [
                        $node->getArgs()[0],
                        new Arg(new Array_(array_map(function ($viewWith) {
                            return new ArrayItem(
                                $viewWith[1]->value,
                                $viewWith[0]->value,
                            );
                        }, array_reverse($this->viewWith)), [
                            'kind' => Array_::KIND_SHORT,
                        ])),
                    ]
                );
            }

            public function leaveNode(Node $node)
            {
                $this->viewWith = [];

                return $this->forViewChain($node, function ($node) {
                    return $node->var;
                });
            }

            public function forViewChain($node, Closure $callback)
            {
                if ($node instanceof Node\Expr\MethodCall
                    && $node->name instanceof Node\Identifier
                    && $node->name->toString() === 'with'
                    && count($node->getArgs()) === 2
                ) {
                    $parent = $node->var;

                    while ($parent instanceof Node\Expr\MethodCall) {
                        $parent = $parent->var;
                    }

                    if ($parent instanceof Node\Expr\FuncCall
                        && $parent->name instanceof Node\Name
                        && $parent->name->toString() === 'view'
                    ) {
                        return $callback($node);
                    }
                }
            }
        };
    }
}
