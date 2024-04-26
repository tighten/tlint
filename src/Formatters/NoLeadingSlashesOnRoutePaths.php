<?php

namespace Tighten\TLint\Formatters;

use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\NoLeadingSlashesOnRoutePaths as Linter;

class NoLeadingSlashesOnRoutePaths extends BaseFormatter
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

        return (new Standard)->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    }

    private function visitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract
        {
            public function enterNode(Node $node): Node|int|null
            {
                if (! $node instanceof Node\Expr\StaticCall) {
                    return null;
                }

                if (! $node->class instanceof Node\Name) {
                    return null;
                }

                if ($node->class->toString() !== 'Route') {
                    return null;
                }

                if (! isset($node->args[0])) {
                    return null;
                }

                if (! $node->args[0]->value instanceof Node\Scalar\String_) {
                    return null;
                }

                if (! Str::startsWith($node->args[0]->value->value, '/')) {
                    return null;
                }

                if ($node->args[0]->value->value === '/') {
                    return null;
                }

                return new StaticCall(
                    $node->class,
                    $node->name,
                    [
                        new Arg(
                            new String_(
                                Str::of($node->args[0]->value->value)->ltrim('/')
                            )
                        ),
                        ...array_slice($node->args, 1),
                    ]
                );
            }
        };
    }
}
