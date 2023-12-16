<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Lexer;
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
use Tighten\TLint\Linters\UseAuthHelperOverFacade as Linter;

class UseAuthHelperOverFacade extends BaseFormatter
{
    public const DESCRIPTION = Linter::DESCRIPTION;

    public const AUTH_SEARCH = '/(?:\\\Illuminate\\\Support\\\Facades\\\)?Auth::([a-zA-Z]+)\(/';

    public const AUTH_HELPER_METHODS = [
        //'routes', // allow routes() to be used
        'extend',
        'provider',
        'loginUsingId',
        'user',
        'guard',
        'createUserProvider',
        'onceBasic',
        'attempt',
        'hasUser',
        'check',
        'guest',
        'once',
        'onceUsingId',
        'validate',
        'viaRemember',
        'logoutOtherDevices',
        'id',
        'login',
        'logout',
        'logoutCurrentDevice',
        'setUser',
        'shouldUse',
    ];

    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return Linter::appliesToPath($path, $configPaths);
    }

    public function format(Parser $parser, Lexer $lexer): string
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);

        $oldStmts = $parser->parse($this->code);
        $newStmts = $traverser->traverse($oldStmts);

        $traverser = new NodeTraverser;
        $traverser->addVisitor($this->visitor());

        $newStmts = $traverser->traverse($newStmts);

        return (new Standard)->printFormatPreserving($newStmts, $oldStmts, $lexer->getTokens());
    }

    private function visitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract
        {
            private bool $useAuthFacade = false;

            public function beforeTraverse(array $nodes)
            {
                $this->useAuthFacade = false;

                return null;
            }

            public function enterNode(Node $node): Node|int|null
            {
                if ($node instanceof Node\Stmt\UseUse && $node->name instanceof Node\Name) {
                    if ($node->name->toString() === 'Illuminate\Support\Facades\Auth') {
                        $this->useAuthFacade = true;
                    }
                }

                if (! $node instanceof Node\Expr\StaticCall) {
                    return null;
                }

                if (! $node->class instanceof Node\Name) {
                    return null;
                }

                if ($this->useAuthFacade && $node->class->toString() !== 'Auth') {
                    return null;
                }

                if (! $this->useAuthFacade && $node->class->toString() !== 'Illuminate\Support\Facades\Auth') {
                    return null;
                }

                if ($node->name->name === 'routes') {
                    return null;
                }

                return new MethodCall(new FuncCall(new Name('auth')), $node->name, $node->args);
            }
        };
    }
}
