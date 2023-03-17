<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsMailables;

class MailableMethodsInBuild extends BaseLinter
{
    use LintsMailables;

    public const DESCRIPTION = 'Mailable values (from and subject etc) should be set in build().';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            static $extendsMailable = false;

            if ($node instanceof Node\Stmt\Class_
                && ! empty($node->extends)
                && $node->extends->toString() === 'Mailable') {
                $extendsMailable = true;
            }

            if ($extendsMailable && $node instanceof Node\Stmt\ClassMethod && $node->name->name === '__construct') {
                foreach ($node->getStmts() as $stmt) {
                    if ($stmt instanceof Node\Stmt\Expression
                        && $stmt->expr instanceof Node\Expr\MethodCall
                        && $stmt->expr->var->name === 'this'
                    ) {
                        return true;
                    }
                }
            }

            return false;
        };
    }
}
