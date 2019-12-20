<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Linters\Concerns\LintsControllers;

class ApplyMiddlewareInRoutes extends BaseLinter
{
    use LintsControllers;
    
    public const description ='Apply middleware in routes (not controllers).';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            static $extendsController = false;

            if ($node instanceof Node\Stmt\Class_
                && $node->extends instanceof Node\Name
                && $node->extends->toString() === 'Controller') {
                $extendsController = true;
            }

            if ($extendsController) {
                return $node instanceof Node\Expr\MethodCall
                    && $node->var instanceof Node\Expr\Variable
                    && $node->var->name === 'this'
                    && $node->name instanceof Node\Identifier
                    && $node->name->name === 'middleware';
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
