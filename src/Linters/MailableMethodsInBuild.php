<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Linters\Concerns\LintsMailables;

class MailableMethodsInBuild extends BaseLinter
{
    use LintsMailables;

    public const description = 'Mailable values (from and subject etc) should be set in build().';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            static $extendsMailable = false;

            if ($node instanceof Node\Stmt\Class_
                && ! empty($node->extends)
                && $node->extends->toString() === 'Mailable') {
                $extendsMailable = true;
            }

            if ($extendsMailable && $node instanceof Node\Stmt\ClassMethod && $node->name->name === '__construct') {
                foreach ($node->getStmts() as $stmt) {
                    if ($stmt->expr && $stmt->expr instanceof Node\Expr\MethodCall
                        && $stmt->expr->var->name === 'this') {
                        return true;
                    }
                }
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
