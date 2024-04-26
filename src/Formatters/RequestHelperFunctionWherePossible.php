<?php

namespace Tighten\TLint\Formatters;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\RequestHelperFunctionWherePossible as Linter;

class RequestHelperFunctionWherePossible extends BaseFormatter
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

        $visitor = $this->visitor();

        $traverser = new NodeTraverser;
        $traverser->addVisitor($visitor);
        $newStmts = $traverser->traverse($newStmts);

        return preg_replace('/\r?\n/', PHP_EOL, (new Standard)->printFormatPreserving($newStmts, $oldStmts, $oldTokens));
    }

    private function visitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract
        {
            public $requestGet = null;

            public function beforeTraverse(array $nodes)
            {
                $this->requestGet = null;

                return null;
            }

            public function enterNode(Node $node): Node|int|null
            {
                $this->forRequestGet($node, function ($node) {
                    $this->requestGet = $node->getArgs()[0];
                });

                if (! $node instanceof Node\Expr\FuncCall) {
                    return null;
                }

                if (! $node->name instanceof Node\Name) {
                    return null;
                }

                if ($node->name->toString() !== 'request') {
                    return null;
                }

                if (! $this->requestGet) {
                    return null;
                }

                return new FuncCall(
                    new Name('request'),
                    [
                        $this->requestGet,
                    ]
                );
            }

            public function leaveNode(Node $node)
            {
                $this->requestGet = null;

                return $this->forRequestGet($node, function ($node) {
                    return $node->var;
                });
            }

            public function forRequestGet($node, Closure $callback)
            {
                if ($node instanceof Node\Expr\MethodCall
                    && $node->name instanceof Node\Identifier
                    && $node->name->toString() === 'get'
                    && count($node->getArgs()) === 1
                ) {
                    $parent = $node->var;

                    while ($parent instanceof Node\Expr\MethodCall) {
                        $parent = $parent->var;
                    }

                    if ($parent instanceof Node\Expr\FuncCall
                        && $parent->name instanceof Node\Name
                        && $parent->name->toString() === 'request'
                    ) {
                        return $callback($node);
                    }
                }
            }
        };
    }
}
