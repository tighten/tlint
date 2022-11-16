<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\RequestValidation as Linter;

class RequestValidation extends BaseFormatter
{
    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return Linter::appliesToPath($path, $configPaths);
    }

    public const DESCRIPTION = Linter::DESCRIPTION;

    public function format(Parser $parser, Lexer $lexer): string
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);

        $oldStmts = $parser->parse($this->code);
        $newStmts = $traverser->traverse($oldStmts);

        $traverser = new NodeTraverser;
        $traverser->addVisitor($this->visitor());
        $newStmts = $traverser->traverse($newStmts);

        return preg_replace('/\r?\n/', PHP_EOL, (new Standard)->printFormatPreserving($newStmts, $oldStmts, $lexer->getTokens()));
    }

    private function visitor(): NodeVisitorAbstract
    {
        return new class() extends NodeVisitorAbstract
        {
            public function enterNode(Node $node): Node|int|null
            {
                if (! $node instanceof Node\Expr\MethodCall) {
                    return null;
                }

                if (! $node->var instanceof Node\Expr\Variable) {
                    return null;
                }

                if ($node->var->name !== 'this') {
                    return null;
                }

                if ($node->name->name !== 'validate') {
                    return null;
                }

                return new Node\Expr\MethodCall(
                    new Node\Expr\FuncCall(
                        new Node\Name('request')
                    ),
                    'validate',
                    $node->args
                );
            }
        };
    }
}
