<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\RequestValidation as Linter;

class RequestValidation extends BaseFormatter
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
            private bool $extendsController = false;

            public function beforeTraverse(array $nodes)
            {
                $this->extendsController = false;

                return null;
            }

            public function enterNode(Node $node): Node|int|null
            {
                if ($node instanceof Node\Stmt\Class_
                    && ! empty($node->extends)
                    && $node->extends->toString() === 'Controller'
                ) {
                    $this->extendsController = true;
                }

                if (! $this->extendsController) {
                    return null;
                }

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

                return new MethodCall(
                    new FuncCall(
                        new Name('request')
                    ),
                    'validate',
                    $node->args
                );
            }
        };
    }
}
